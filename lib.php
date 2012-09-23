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
 * Plugin api implementation library for rawrecordscount report
 *
 * All the functions and callbacks required in the report plugin
 * to interact with different parts of the core
 *
 * @package    report_rawrecordscount
 * @copyright  2011 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This function extends the navigation with the (course) report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_rawrecordscount_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/rawrecordscount:view', $context)) {
        $url = new moodle_url('/report/rawrecordscount/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_rawrecordscount'), $url,
            navigation_node::TYPE_SETTING, null, null, new pix_icon('icon', '', 'report_rawrecordscount'));
    }
}
