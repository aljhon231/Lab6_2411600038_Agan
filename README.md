# Student Portal Management System

This Laravel application implements the Lab 6 course/enrollment adaptation and
runs separately from the original static Student Portal in the parent folder.

## Requirements and setup

- PHP 8.3 or later
- Composer

From this folder, run:

```sh
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8001
```

Open `http://127.0.0.1:8001`. Port 8001 avoids conflict with the other Lab
project using port 8000. SQLite is the default database for a fresh checkout.

Register a student account from the login page. To grant administrator access
to a registered user, run this command from the project folder:

```sh
php artisan portal:make-admin student@example.com
```

## Implemented features

- Student registration, login, logout, and authenticated portal pages.
- Course catalog with search, availability filters, course details, and
  admin-only course CRUD; courses with enrollment records cannot be deleted.
- Enrollment and cancellation in a database transaction, with one active
  enrollment per student/course and a recorded history of every action.
- Observer-managed slot updates, course-capacity validation, and database
  notifications for administrators when a course reaches its few-slots
  threshold.
- Admin-only slot adjustments with a required reason and optional reference.
- Dashboard statistics API, almost-full courses, recent activity, and
  30-second AJAX polling.
- Enrollment, course-availability, and transaction reports with filters.
- Admin permissions enforced by authorization middleware; transaction history
  remains append-only.

## Tests

```sh
php artisan test
```

## Lab 6 submission

The report and evidence screenshots are in `submission/`. The current local
evidence run uses the dedicated MySQL database `lab6_student_portal`; in
phpMyAdmin, open that database, select `courses`, and choose **Browse**. The
course rows are also captured in
`submission/screenshots/10-mysql-courses.png`. Fresh checkouts still use SQLite
unless their local `.env` selects another database connection.
