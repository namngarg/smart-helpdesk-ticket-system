# Smart Helpdesk & Ticket Management System

A PHP/MySQL ticketing system with role-based access:

- **User** — creates support tickets and tracks their status.
- **Admin** — assigns tickets to employees, sets priority/deadline, manages users.
- **Employee** — updates the status of tickets assigned to them.
- **Dashboard** — ticket analytics (by status, by priority, overdue count).

## Setup

1. Make sure Apache + MySQL are running (e.g. via XAMPP/LAMPP).
2. Import the schema and seed data:
   ```
   mysql -u root -p < database.sql
   ```
3. Check the DB credentials in `config/db.php` match your local MySQL setup.
4. Open the project in your browser, e.g. `http://localhost/<project-folder>/`.

## Default accounts (seeded)

| Role     | Email                  | Password      |
|----------|-------------------------|---------------|
| Admin    | admin@helpdesk.com      | admin123      |
| Employee | employee1@helpdesk.com  | employee123   |

New users can self-register (always created with the `user` role). Admin accounts create additional employee/admin accounts from **Manage Users**.
