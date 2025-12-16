# Running Database Migrations

If your `admins` table does not yet include the `role` column, run the project's migration script to add it and bring your schema up-to-date.

Options:

- Via browser (easy):

  1. Open your browser and visit:

     `http://localhost/barcie_php/database/update_database.php`

  2. Follow the on-screen output. The script will run migrations including `002_update_admins_table.php` and add the `role` column if missing.

- Via CLI (developer):

  Open a PowerShell window and run:

```powershell
php C:\xampp\htdocs\barcie_php\database\update_database.php
```

Notes:

- The migration script is idempotent and safe to run multiple times.
- The migration will also hash any plaintext admin passwords it finds.
- After running the migration, log in again to ensure your session includes the `admin_role` value.
