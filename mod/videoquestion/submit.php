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
 * Handle question submission for mod_videoquestion.
 *
 * @package    mod_videoquestion
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$cmid = required_param('id', PARAM_INT);
$questiontext = required_param('question', PARAM_RAW_TRIMMED);

// Confirm sesskey for CSRF protection.
require_sesskey();

// Fetch course module and context.
$cm = get_coursemodule_from_id('videoquestion', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$videoquestion = $DB->get_record('videoquestion', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);

// Login and capability checks.
require_login($course, true, $cm);
require_capability('mod/videoquestion:submitquestion', $context);

$redirecturl = new moodle_url('/mod/videoquestion/view.php', ['id' => $cm->id]);

try {
    \mod_videoquestion\local\question_manager::add_question(
        $videoquestion->id,
        $USER->id,
        $questiontext
    );
    redirect($redirecturl, get_string('questionsubmitted', 'mod_videoquestion'), null, \core\output\notification::NOTIFY_SUCCESS);
} catch (\moodle_exception $e) {
    redirect($redirecturl, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
}
