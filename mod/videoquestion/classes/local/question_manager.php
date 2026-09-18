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

namespace mod_videoquestion\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Question manager class for mod_videoquestion.
 *
 * @package    mod_videoquestion
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_manager {

    /**
     * Normalize a question string:
     * 1. Trim leading and trailing whitespace.
     * 2. Collapse internal multiple whitespace to a single space.
     * 3. Convert text to lowercase using Moodle core_text/mb_strtolower.
     *
     * @param string $question
     * @return string
     */
    public static function normalize_question(string $question): string {
        // Trim leading and trailing whitespace.
        $trimmed = trim($question);

        // Collapse multiple whitespace characters into a single space.
        $collapsed = preg_replace('/\s+/u', ' ', $trimmed);

        // Convert text to lowercase using Moodle's UTF-8 wrapper if available, or mb_strtolower.
        if (class_exists('\core_text')) {
            return \core_text::strtolower($collapsed);
        }
        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * Compute SHA-256 hash of normalized question.
     *
     * @param string $normalized
     * @return string
     */
    public static function hash_normalized_question(string $normalized): string {
        return hash('sha256', $normalized);
    }

    /**
     * Check whether a normalized version of the question has already been submitted.
     *
     * @param int $videoquestionid
     * @param string $question
     * @return bool
     */
    public static function is_duplicate(int $videoquestionid, string $question): bool {
        global $DB;

        $normalized = self::normalize_question($question);
        if ($normalized === '') {
            return false;
        }

        $hash = self::hash_normalized_question($normalized);

        return $DB->record_exists('videoquestion_questions', [
            'videoquestionid' => $videoquestionid,
            'normalizedhash' => $hash,
        ]);
    }

    /**
     * Add a question if it is not a duplicate.
     *
     * @param int $videoquestionid
     * @param int $userid
     * @param string $question
     * @return int Inserted question record ID.
     * @throws \moodle_exception If validation fails or duplicate exists.
     */
    public static function add_question(int $videoquestionid, int $userid, string $question): int {
        global $DB;

        $normalized = self::normalize_question($question);

        if ($normalized === '') {
            throw new \moodle_exception('questionempty', 'mod_videoquestion');
        }

        if (mb_strlen($question, 'UTF-8') > 2000) {
            throw new \moodle_exception('questiontoolong', 'mod_videoquestion');
        }

        if (self::is_duplicate($videoquestionid, $question)) {
            throw new \moodle_exception('duplicatequestion', 'mod_videoquestion');
        }

        $hash = self::hash_normalized_question($normalized);
        $now = time();
        $record = new \stdClass();
        $record->videoquestionid = $videoquestionid;
        $record->userid = $userid;
        $record->question = trim($question);
        $record->normalizedquestion = $normalized;
        $record->normalizedhash = $hash;
        $record->timecreated = $now;
        $record->timemodified = $now;

        try {
            return $DB->insert_record('videoquestion_questions', $record);
        } catch (\dml_write_exception $e) {
            // Check if duplicate key violation occurred during race condition.
            if (self::is_duplicate($videoquestionid, $question)) {
                throw new \moodle_exception('duplicatequestion', 'mod_videoquestion');
            }
            throw $e;
        }
    }

    /**
     * Retrieve accepted questions for an activity with author info.
     *
     * @param int $videoquestionid
     * @return array
     */
    public static function get_questions(int $videoquestionid): array {
        global $DB;

        $userfieldsapi = \core_user\fields::for_name();
        $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT q.id, q.videoquestionid, q.userid, q.question, q.timecreated, $userfields
                  FROM {videoquestion_questions} q
                  JOIN {user} u ON u.id = q.userid
                 WHERE q.videoquestionid = :vqId
              ORDER BY q.id ASC";

        return $DB->get_records_sql($sql, ['vqId' => $videoquestionid]);
    }
}
