# BugOut

Bug tracking web app.

## Local run

This project uses **PHP + MySQL** (the `api/` endpoints are PHP).

1) Set environment variables for the DB connection:

- `BUGOUT_DB_HOST`
- `BUGOUT_DB_NAME`
- `BUGOUT_DB_USER`
- `BUGOUT_DB_PASS`

2) Start a local server from the repo root:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

## Deployment note

**GitHub Pages cannot run PHP**, so sign-in and other `api/*.php` endpoints will not work on GitHub Pages. If you want this hosted publicly, we can deploy the PHP app to a PHP-capable host (and optionally keep the static frontend on GitHub Pages).