# Video Question (mod_videoquestion)

Moodle Activity Plugin for interactive video questions and exact duplicate prevention.

## Local Development & Demo Environment

- URL: `http://localhost:8080`
- Stack: Moodle 4.4 + MariaDB 10.11 via Docker Compose

### Demo Accounts

| Role | Username | Password | Purpose |
|---|---|---|---|
| **Admin** | `admin` | `AdminPassword123!` | Full administration |
| **Teacher** | `teacher1` | `TeacherPassword123!` | Manage course & activities |
| **Student 1** | `student1` | `StudentPassword123!` | Test initial question submission |
| **Student 2** | `student2` | `StudentPassword123!` | Test duplicate submission & retry |

### Demo Links
- Course: [Video Question Demo](http://localhost:8080/course/view.php?id=2)
- Activity: [Introduction to Blockchain](http://localhost:8080/mod/videoquestion/view.php?id=3)