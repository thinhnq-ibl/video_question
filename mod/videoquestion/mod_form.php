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
 * The main mod_videoquestion configuration form.
 *
 * @package    mod_videoquestion
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 */
class mod_videoquestion_mod_form extends moodleform_mod {

    /**
     * Defines the form.
     */
    public function definition() {
        $mform = $this->_form;

        // Adding the "General" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('videoquestionname', 'mod_videoquestion'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Video URL field.
        $mform->addElement('text', 'video_url', get_string('videourl', 'mod_videoquestion'), ['size' => '64']);
        $mform->setType('video_url', PARAM_URL);
        $mform->addRule('video_url', null, 'required', null, 'client');
        $mform->addHelpButton('video_url', 'videourl', 'mod_videoquestion');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements(get_string('instructions', 'mod_videoquestion'));

        // Add standard elements (course module settings, restrict access, etc.).
        $this->standard_coursemodule_elements();

        // Standard buttons.
        $this->add_action_buttons();
    }
}
