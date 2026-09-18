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
 * Library of functions for mod_videoquestion.
 *
 * @package    mod_videoquestion
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Checks if mod_videoquestion supports a specific feature.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if supported, false if not, null if not understood
 */
function videoquestion_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Add a new instance of videoquestion.
 *
 * @param stdClass $data
 * @param mod_videoquestion_mod_form $mform
 * @return int new instance id
 */
function videoquestion_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    return $DB->insert_record('videoquestion', $data);
}

/**
 * Update an existing instance of videoquestion.
 *
 * @param stdClass $data
 * @param mod_videoquestion_mod_form $mform
 * @return bool success
 */
function videoquestion_update_instance($data, $mform = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('videoquestion', $data);
}

/**
 * Delete an instance of videoquestion and associated questions.
 *
 * @param int $id
 * @return bool success
 */
function videoquestion_delete_instance($id) {
    global $DB;

    if (!$record = $DB->get_record('videoquestion', ['id' => $id])) {
        return false;
    }

    // Delete all questions associated with this activity.
    $DB->delete_records('videoquestion_questions', ['videoquestionid' => $id]);

    // Delete the activity itself.
    $DB->delete_records('videoquestion', ['id' => $id]);

    return true;
}
