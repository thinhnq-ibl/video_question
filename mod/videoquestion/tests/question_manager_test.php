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

namespace mod_videoquestion;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../classes/local/question_manager.php');

/**
 * Unit tests for question_manager.
 *
 * @package    mod_videoquestion
 * @category   test
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_manager_test extends \advanced_testcase {

    public function test_normalize_question() {
        // Case insensitive normalization
        $this->assertEquals(
            'hello world?',
            \mod_videoquestion\local\question_manager::normalize_question('Hello World?')
        );

        // Whitespace collapse & trim
        $this->assertEquals(
            'what is blockchain?',
            \mod_videoquestion\local\question_manager::normalize_question("   what   is \t blockchain? \n  ")
        );

        // UTF-8 multibyte characters
        $this->assertEquals(
            'hỏi về blockchain?',
            \mod_videoquestion\local\question_manager::normalize_question('  HỎI VỀ   BLOCKCHAIN?  ')
        );
    }

    public function test_duplicate_and_acceptance() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Create a real videoquestion activity record.
        $vqrecord = new \stdClass();
        $vqrecord->course = $course->id;
        $vqrecord->name = 'Test Video Activity';
        $vqrecord->intro = '<p>Watch and ask questions</p>';
        $vqrecord->introformat = FORMAT_HTML;
        $vqrecord->video_url = 'https://www.youtube.com/watch?v=test';
        $vqrecord->timecreated = time();
        $vqrecord->timemodified = time();
        $vqId = $DB->insert_record('videoquestion', $vqrecord);

        // Add first question
        $id1 = \mod_videoquestion\local\question_manager::add_question(
            $vqId,
            $user1->id,
            'Why does blockchain need consensus?'
        );
        $this->assertNotEmpty($id1);

        // Duplicate check on identical string
        $this->assertTrue(
            \mod_videoquestion\local\question_manager::is_duplicate($vqId, 'Why does blockchain need consensus?')
        );

        // Duplicate check on case & whitespace variation
        $this->assertTrue(
            \mod_videoquestion\local\question_manager::is_duplicate($vqId, '   WHY   DOES BLOCKCHAIN NEED CONSENSUS?   ')
        );

        // Attempting to add duplicate should throw exception
        $this->expectException(\moodle_exception::class);
        \mod_videoquestion\local\question_manager::add_question(
            $vqId,
            $user2->id,
            '   WHY   DOES BLOCKCHAIN NEED CONSENSUS?   '
        );
    }

    public function test_different_question_accepted() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Create a real videoquestion activity record.
        $vqrecord = new \stdClass();
        $vqrecord->course = $course->id;
        $vqrecord->name = 'Test Video Activity 2';
        $vqrecord->intro = '<p>Watch and ask questions</p>';
        $vqrecord->introformat = FORMAT_HTML;
        $vqrecord->video_url = 'https://www.youtube.com/watch?v=test2';
        $vqrecord->timecreated = time();
        $vqrecord->timemodified = time();
        $vqId = $DB->insert_record('videoquestion', $vqrecord);

        $id1 = \mod_videoquestion\local\question_manager::add_question(
            $vqId,
            $user1->id,
            'Why does blockchain need consensus?'
        );

        // Semantically similar but different text -> accepted in v0.1
        $id2 = \mod_videoquestion\local\question_manager::add_question(
            $vqId,
            $user2->id,
            'Why is consensus important in blockchain?'
        );

        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2);
    }
}
