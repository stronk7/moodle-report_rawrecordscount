<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main file for rawrecordscount report
 *
 * One simple course report to show the number of log entries per
 * student in a given course. Downloadable in various formats.
 *
 * @package    report
 * @subpackage rawrecordscount
 * @copyright  2009 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/excellib.class.php');
require_once($CFG->libdir.'/odslib.class.php');

$id  = required_param('id', PARAM_INT); // course id.
$out = optional_param('out', 'html', PARAM_TAG);   // output (html, xls, ods, txt) defaults to html

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/report/rawrecordscount/index.php', array('id' => $id, 'out' => $out));
$PAGE->set_pagelayout('report');

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('report/rawrecordscount:view', $context);

// Group calculations
$groupmode    = groups_get_course_groupmode($course);   // Groups are being used
$currentgroup = groups_get_course_group($course, true); // Fetches current selected group
if (!$currentgroup) {      // To make some other functions work better later
    $currentgroup  = NULL;
}

add_to_log($course->id, 'course', 'report rawrecordscount', "report/rawrecordscount/index.php?id=$course->id", $course->id);

$strreports = get_string('reports');
$strusers = get_string('users');
$strcount = get_string('count', 'report_rawrecordscount');
$strname = get_string('pluginname', 'report_rawrecordscount');
$strnameheading = get_string('rawrecordsreportcount', 'report_rawrecordscount');
$strfilename = get_string('rawrecordsreportfilename', 'report_rawrecordscount');

// Calculate recordset (common for all outputs)

// Get the list of all users having moodle/course:manageactivities capability
// in order to take out them later from the report (to try to get "students" only)
if ($havemanage = get_users_by_capability($context, 'moodle/course:manageactivities', 'u.id')) {
    $havemanage = array_keys($havemanage);
}
list ($msql, $mparams) = $DB->get_in_or_equal($havemanage, SQL_PARAMS_NAMED, 'man', false, 0);

// Crap we cannot delimite to have also some ras, or introduce a negative cap lookup,
// or check them against some role archetype, or have one "exlusive" cap to look for,
// or check against CFG->gradeableroles, or whatever, grrr!
// This makes the point of taking out teachers (course:manageactivities)
// by hand necessary (and imperfect for // situations with multiple roles). grrr^2!
list($esql, $eparams) = get_enrolled_sql($context, '', $currentgroup);

$ufields = user_picture::fields('u');
$sql = "SELECT $ufields , count(l.id) as count
          FROM {user} u
          JOIN ($esql) je ON je.id = u.id
     LEFT JOIN {log} l on l.userid = u.id AND l.course = :course
         WHERE u.id $msql
      GROUP BY " . user_picture::fields('u') . "
      ORDER BY u.lastname, u.firstname";

$params = $eparams + $mparams + array('course' => $course->id);;

$rs = $DB->get_recordset_sql($sql, $params);

// Content output (different based on $out)

if ($out == 'xls') { // XLS output
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($strfilename . '.xls');
    $worksheet =& $workbook->add_worksheet($strfilename);

    $worksheet->write(0, 0, $strusers);
    $worksheet->write(0, 1, $strcount);

    $row = 1;

    foreach ($rs as $rec) {
        $user = new stdClass();
        $user->id = $rec->id;
        $user->firstname = $rec->firstname;
        $user->lastname = $rec->lastname;
        $user->picture = $rec->picture;
        $user->imagealt = $rec->imagealt;
        $user->email = $rec->email;

        $worksheet->write($row, 0, fullname($user));
        $worksheet->write($row, 1, $rec->count);

        $row++;
    }
    $workbook->close();

} else if ($out == 'ods') { // ODS output
    $workbook = new MoodleODSWorkbook('-');
    $workbook->send($strfilename . '.ods');
    $worksheet =& $workbook->add_worksheet($strfilename);

    $worksheet->write(0, 0, $strusers);
    $worksheet->write(0, 1, $strcount);

    $row = 1;

    foreach ($rs as $rec) {
        $user = new stdClass();
        $user->id = $rec->id;
        $user->firstname = $rec->firstname;
        $user->lastname = $rec->lastname;
        $user->imagealt = $rec->imagealt;
        $user->email = $rec->email;
        $user->picture = $rec->picture;

        $worksheet->write($row, 0, fullname($user));
        $worksheet->write($row, 1, $rec->count);

        $row++;

    }
    $workbook->close();

} else if ($out == 'txt') { // CSV output
    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename={$strfilename}.txt");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    echo $strusers . "\t" . $strcount . "\n";

    $row = 1;

    foreach ($rs as $rec) {
        $user = new stdClass();
        $user->id = $rec->id;
        $user->firstname = $rec->firstname;
        $user->lastname = $rec->lastname;
        $user->picture = $rec->picture;
        $user->imagealt = $rec->imagealt;
        $user->email = $rec->email;

        echo fullname($user) . "\t" . $rec->count . "\n";

        $row++;

    }

} else { // HTML output
    $PAGE->set_title($course->shortname .': '. $strname);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strnameheading);

    // Print groups selector
    groups_print_course_menu($course, 'index.php?id=' . $course->id);

    // Print download selector
    $options = array('xls' => get_string('downloadexcel'),
                     'ods' => get_string('downloadods'),
                     'txt' => get_string('downloadtext'));
    echo $OUTPUT->single_select(new moodle_url('/report/rawrecordscount/index.php',
                                          array('id' => $course->id, 'group' => $currentgroup)),
                           'out', $options, $currentgroup);

    echo '<div class="clearer">&nbsp;</div>';

    $table = new html_table();
    $table->width = '90%';
    $table->head = array('&nbsp;', $strusers, $strcount);
    $table->align = array('center', 'left', 'center');
    $table->size = array('20%', '60%', '20%');

    foreach ($rs as $rec) {
        $userpicture = $OUTPUT->user_picture($rec);
        $userfullname = fullname($rec);

        $table->data[] = new html_table_row(array($userpicture, $userfullname, $rec->count));
    }
    echo html_writer::table($table);

    echo $OUTPUT->footer();
}

// Close the recordset (common for all outputs)
$rs->close();
