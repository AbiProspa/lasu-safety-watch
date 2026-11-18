# Deploying LASU Safety Watch on Railway

Railway runs two things: **the app** (built from this repo's `Dockerfile`) and **a MySQL database**.
Vercel is not used, because it cannot run a Laravel + MySQL app properly.

## 1. Put the code on GitHub

```bash
cd lasu-safety-watch
git init
git add .
git commit -m "LASU Safety Watch"
git branch -M main
git remote add origin https://github.com/<your-username>/lasu-safety-watch.git
git push -u origin main
```

The `.env` file and `database.sqlite` are ignored and will not be uploaded.

## 2. Create the project on Railway

1. railway.com → **New Project** → **Deploy from GitHub repo** → choose `lasu-safety-watch`.
   Railway sees the `Dockerfile` and `railway.json` and builds with them.
2. In the same project: **+ New** → **Database** → **Add MySQL**.

## 3. Set the app's variables

Open the app service → **Variables** → **Raw Editor** and paste:

```
APP_NAME="LASU Safety Watch"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:PASTE_YOUR_KEY_HERE
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_TIMEZONE=Africa/Lagos
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
SESSION_DRIVER=database
CACHE_STORE=database
SEED_DEMO_REPORTS=true
```

- **APP_KEY:** run `php artisan key:generate --show` on your PC and paste the result (it starts with `base64:`).
- **SEED_DEMO_REPORTS:** `true` loads 20 sample reports on the first deploy. Set it to `false` before real use.

## 4. Public URL and photo storage

- **Settings → Networking → Generate Domain.** Railway gives you a URL like `https://lasu-safety-watch-production.up.railway.app`.
- **Volume for uploaded photos:** right-click the app service → **Attach Volume** → mount path:
  `/var/www/html/storage/app/public`

## 5. Deploy

Railway builds and deploys automatically (about 3–6 minutes). On every start the container:
1. runs the database migrations,
2. creates the categories and staff accounts if they are missing (it never resets changed passwords),
3. starts the web server.

Open your Railway URL and log in with `admin@lasu.test` / `Admin@1234`.

## 6. Before real use

1. **Users & roles** → change every demo password, or disable the demo accounts.
2. In `config/lsw.php`, replace the `0800000000x` phone numbers with the official LASU numbers. Check the landmark coordinates too.
3. Set `SEED_DEMO_REPORTS=false`.
4. Push to GitHub. Railway redeploys by itself.

## Troubleshooting

| Problem | Fix |
|---|---|
| Deploy fails with "SQLSTATE … Connection refused" | The `DB_*` variables must reference the database service by its exact name (`MySQL`). |
| "No application encryption key" error | `APP_KEY` is missing or doesn't start with `base64:`. |
| Pages load without styling, or links use `http://` | Check that `APP_URL` is your `https://` Railway domain. |
| Uploaded photos disappear after a redeploy | Attach the volume (step 4). |
| See what went wrong | Open the service → **Deployments** → **View logs**. |
