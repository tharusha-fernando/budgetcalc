# Deploy notes

## Stack

- Laravel + Filament admin
- SQLite in production (example host: `budgetbudget.mihimatha.com`)
- Vite Filament theme (rebuild assets if CSS looks unstyled)

## Production checklist

1. Set `.env`: `APP_URL`, `APP_KEY`, SQLite `DB_CONNECTION=sqlite` and `DB_DATABASE` path.
2. Install PHP SQLite extension if missing (e.g. `php8.3-sqlite3` / enable `pdo_sqlite`).
3. Make sure the web user can write:
   - `storage/`
   - `bootstrap/cache/`
   - the SQLite database file and its directory
4. Run:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan view:cache
```

5. If the Filament UI looks broken (raw Tailwind classes), rebuild frontend assets (`npm run build`) and deploy `public/build`.

## Attachments migration

Payments to guy need:

`2026_11_21_160000_add_attachments_to_restoration_payments_table`

After deploy: `php artisan migrate --force`.

## Seeded login (local / demo)

If the database was seeded with the app’s default seeder:

- Email: `admin@budget.test`
- Password: `password`

Change this in production.

## Git remotes

Current work has been pushed to the `budgetcalc` remote in past sessions; confirm `git remote -v` before pushing.
