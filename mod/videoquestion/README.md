# Video Question (mod_videoquestion)

A minimal Moodle activity plugin that requires students to submit questions about learning videos while preventing duplicate questions.

## MVP v0.1 Features
- Create Video Question activity within any Moodle course.
- Configure video URL (with automatic YouTube embedded preview and direct link).
- Student question submission with CSRF, capability, and validation checks.
- Exact duplicate detection based on normalized question content (trimmed, collapsed whitespace, lowercase).
- Render accepted questions list via Mustache template.
- Privacy & capability awareness: Author names rendered conditionally according to `mod/videoquestion:viewquestions`.

## Normalization & Duplicate Checking
Before checking or storing:
1. Trim leading and trailing whitespace.
2. Collapse repeated whitespace into a single space.
3. Convert text to lowercase using UTF-8 functions (`core_text::strtolower` or `mb_strtolower`).

Example:
`"What is Blockchain?"` and `"   what   is blockchain?   "` are treated as identical duplicates.

## Architectural Decisions & Hardening (v0.1)
- **Duplicate Invariant Protection**: An XMLDB unique index `vq_norm_idx` (`videoquestionid`, `normalizedquestion`) prevents race-condition duplicates at the database level. Application-level `is_duplicate()` checks remain for friendly error messaging.
- **Guest Access Decision**: In `db/access.php`, `mod/videoquestion:view` is restricted to authenticated enrolled roles (`student`, `teacher`, `editingteacher`, `manager`). Guests are not allowed because video submission and classroom question workflows are tied to authenticated enrolled students.
- **Backup/Restore Support**: `FEATURE_BACKUP_MOODLE2` is not declared for v0.1 as the backup/restore stepslib is not yet implemented.
- **Moodle Privacy API (TODO)**: Plugin stores user IDs, question text, and timestamps (`userid`, `question`, `timecreated`). Privacy API implementation (`classes/privacy/provider.php`) is scheduled for future releases prior to submission to the Moodle Plugins Directory.

## Requirements
- Moodle 4.0+ (PHP 8.0 / 8.1 / 8.2 compatible).
- PHP `mbstring` extension enabled.

## Installation
1. Copy or clone this directory into your Moodle installation under `mod/videoquestion`:
   ```bash
   cp -r mod/videoquestion /path/to/moodle/mod/videoquestion
   ```
2. Navigate to **Site administration -> Notifications** to execute the database installation and upgrade.
3. Add the activity to any course via **Add an activity or resource -> Video Question**.
