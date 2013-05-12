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
 * Version file for rawrecordscount report
 *
 * @package    report_rawrecordscount
 * @copyright  2009 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2013051200; // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires = 2012062500; // Requires this Moodle version (v2.3 release).
$plugin->component = 'report_rawrecordscount'; // Full name of the plugin.
$plugin->maturity = MATURITY_STABLE; // Maturity of the plugin.
$plugin->release = '2.3.0'; // Release name of the plugin.
