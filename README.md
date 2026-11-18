# LASU Safety Watch

**Design and Implementation of a Web-Based Crowdsourced Security and Incident Reporting System for Lagos State University (LASU).**

Students, staff and visitors report security threats and campus problems (theft, cult activity, harassment, hostel problems, broken facilities, flooding and medical emergencies) with a description, a photo and a map pin. They can report anonymously. Each report goes straight to the LASU unit responsible for it. That unit verifies the report, assigns an officer, and tracks it until it is resolved. A one-tap **SOS button** sends the user's GPS location to Campus Security.

Built with **Laravel 12, MySQL, Tailwind CSS, Alpine.js, Leaflet (OpenStreetMap) and Chart.js**. UI rules: see `.claude/CLAUDE.md` in the parent workspace.

## Run it on your PC

Requirements: PHP 8.2+, Composer and Node.js. You do **not** need MySQL locally, because it uses SQLite out of the box.

```bash
composer install
npm install
npm run build
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Then open http://127.0.0.1:8000.

To use XAMPP MySQL instead, set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, create the database, then run `php artisan migrate:fresh --seed`.

### Demo accounts

These are demo passwords. Change them before real use.

| Role | Email | Password |
|---|---|---|
| Administrator | admin@lasu.test | Admin@1234 |
| Campus Security | security@lasu.test | Security@123 |
| Security officer | bello@lasu.test | Security@123 |
| Works & Services | works@lasu.test | Works@1234 |
| Medical Centre | medical@lasu.test | Medical@123 |
| Counselling | counsel@lasu.test | Counsel@123 |
| Student | student@lasu.test | Student@123 |

## Deploy

- **Live (free):** https://lasu-safety-watch.onrender.com, on Render + TiDB Cloud (MySQL). See [DEPLOY-FREE.md](DEPLOY-FREE.md).
- **Paid (always on):** Railway, see [DEPLOY-RAILWAY.md](DEPLOY-RAILWAY.md).

## Where things are

| What | File / folder |
|---|---|
| Campus locations, emergency numbers, units, roles, statuses | `config/lsw.php` |
| Database tables | `database/migrations/2026_11_17_000001_create_safety_tables.php` |
| Categories, staff accounts, demo reports | `database/seeders/` |
| Public pages (home, report, map, SOS, track) | `app/Http/Controllers/PublicController.php`, `ReportController.php` |
| Staff dashboard, report workflow, CSV export | `app/Http/Controllers/Staff/` |
| User management (admin) | `app/Http/Controllers/Admin/UserController.php` |
| Who can see what (roles) | `app/Http/Middleware/EnsureRole.php`, `app/Models/User.php` |
| Page designs | `resources/views/` |
| Map, SOS button, charts (JavaScript) | `resources/js/app.js` |
| Theme / colours | `resources/css/app.css` |
| Project explanation (Word + PDF) | `docs/` |
