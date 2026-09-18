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
 * View a videoquestion instance.
 *
 * @package    mod_videoquestion
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course module ID.
$v  = optional_param('v', 0, PARAM_INT);  // videoquestion instance id.

if ($id) {
    $cm = get_coursemodule_from_id('videoquestion', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $videoquestion = $DB->get_record('videoquestion', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($v) {
    $videoquestion = $DB->get_record('videoquestion', ['id' => $v], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $videoquestion->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('videoquestion', $videoquestion->id, $course->id, false, MUST_EXIST);
} else {
    throw new \moodle_exception('missingparameter');
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videoquestion:view', $context);

$PAGE->set_url('/mod/videoquestion/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($videoquestion->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($videoquestion->name));

// Display instructions / intro.
if (!empty($videoquestion->intro)) {
    echo $OUTPUT->box(format_module_intro('videoquestion', $videoquestion, $cm->id), 'generalbox mod_introbox', 'videoquestionintro');
}

// Display Video link / embed container.
$videourl = clean_param($videoquestion->video_url, PARAM_URL);
echo html_writer::start_div('videoquestion-video-box card p-3 mb-4');
echo html_writer::tag('h4', get_string('videourl', 'mod_videoquestion'), ['class' => 'card-title']);

// Safe YouTube embed helper or fallback link.
$ytmatches = [];
if (preg_match('~(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})~i', $videourl, $ytmatches)) {
    $embedurl = 'https://www.youtube.com/embed/' . s($ytmatches[1]);
    echo html_writer::tag('div',
        html_writer::empty_tag('iframe', [
            'src' => $embedurl,
            'width' => '100%',
            'height' => '420',
            'frameborder' => '0',
            'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
            'allowfullscreen' => 'allowfullscreen',
            'class' => 'rounded',
        ]),
        ['class' => 'ratio ratio-16x9 mb-2', 'style' => 'max-width: 720px;']
    );
}

echo html_writer::tag('p',
    html_writer::link($videourl, get_string('videolink', 'mod_videoquestion', s($videourl)), ['target' => '_blank', 'rel' => 'noopener noreferrer', 'class' => 'btn btn-outline-primary btn-sm'])
);
echo html_writer::end_div();

// Question submission form if user has capability.
if (has_capability('mod/videoquestion:submitquestion', $context)) {
    echo html_writer::start_div('videoquestion-submit-form card p-3 mb-4');
    echo html_writer::tag('h4', get_string('askquestion', 'mod_videoquestion'), ['class' => 'card-title']);

    $submitaction = new moodle_url('/mod/videoquestion/submit.php');
    echo html_writer::start_tag('form', ['action' => $submitaction, 'method' => 'post', 'class' => 'm-t-1']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo html_writer::start_div('form-group mb-3');
    echo html_writer::tag('label', get_string('askquestion', 'mod_videoquestion'), ['for' => 'question-input', 'class' => 'form-label']);
    echo html_writer::tag('textarea', '', [
        'name' => 'question',
        'id' => 'question-input',
        'class' => 'form-control',
        'rows' => 3,
        'maxlength' => 2000,
        'required' => 'required',
        'placeholder' => get_string('askquestion', 'mod_videoquestion') . '...',
    ]);
    echo html_writer::end_div();

    echo html_writer::tag('button', get_string('submitquestion', 'mod_videoquestion'), [
        'type' => 'submit',
        'class' => 'btn btn-primary',
    ]);

    echo html_writer::end_tag('form');
    echo html_writer::end_div();
}

// Display questions list.
$canviewquestions = has_capability('mod/videoquestion:viewquestions', $context);
$records = \mod_videoquestion\local\question_manager::get_questions($videoquestion->id);

$questionlistdata = [];
$idx = 1;
foreach ($records as $rec) {
    $authorname = fullname($rec);
    $questionlistdata[] = [
        'index' => $idx++,
        'question' => s($rec->question),
        'author' => s($authorname),
        'hasauthor' => $canviewquestions,
    ];
}

$templatedata = [
    'hasquestions' => !empty($questionlistdata),
    'canviewquestions' => $canviewquestions,
    'questions' => $questionlistdata,
];

echo $OUTPUT->render_from_template('mod_videoquestion/question_list', $templatedata);

echo $OUTPUT->footer();
