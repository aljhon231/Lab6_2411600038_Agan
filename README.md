<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://laravel.com/img/logomark.min.svg" width="96" alt="Laravel logo">
  </a>
</p>

<h1 align="center">Student Portal Management System</h1>

<p align="center">
  <strong>Laravel Lab 6 · Course and Enrollment Management</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.3 or later">
  <img src="https://img.shields.io/badge/Project-Lab%206-16A34A" alt="Lab 6 project">
</p>

---

## About this project

A course and enrollment management portal built for the Lab 6 requirements.
Students can browse courses and manage enrollments, while administrators
maintain course offerings, review activity, and monitor available slots.

The Laravel application source is in [`Laravel/`](./Laravel/). Open that folder
in VS Code to work directly with the app.

## Features

- Student registration, login, course catalog, enrollment, and cancellation.
- Admin-only course management and slot adjustments.
- Transaction history and database-backed notifications.
- Dashboard statistics, recent activity, and enrollment reports.
- Responsive pages with a custom green color scheme.

## Run locally

From the repository root:

```sh
cd Laravel
composer install
Copy-Item .env.example .env
New-Item -ItemType File database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8001
```

Then open `http://127.0.0.1:8001`. This fresh-checkout setup uses SQLite.
For more details and administrator setup, see the
[`Laravel/README.md`](./Laravel/README.md).

## Project links

- [Laravel source and setup instructions](./Laravel/)
- [Lab 6 report and evidence screenshots](./Laravel/submission/)
- [phpMyAdmin courses-table evidence](./Laravel/submission/screenshots/10-mysql-courses.png)

## Author

**aljhon231**
