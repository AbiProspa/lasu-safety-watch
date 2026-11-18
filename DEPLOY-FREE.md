# Free deployment: Render (app) + Aiven (MySQL)

The whole setup is free and needs no card:

| Part | Service | Free plan |
|---|---|---|
| Web app | **Render** web service, built from this repo's `Dockerfile` | Sleeps after 15 min idle; the first visit after that takes ~1 minute |
| Database | **Aiven** for MySQL | 1 small MySQL database, does not expire |

> Uploaded photos are stored on Render's temporary disk and are lost when the app restarts or redeploys.
> For a defence or demo this is fine. For long-term use, move to a paid plan with a persistent disk (e.g. Railway, see `DEPLOY-RAILWAY.md`).

## 1. Create the MySQL database (Aiven)

1. Sign up at **aiven.io** (GitHub or Google sign-in works).
2. **Create service → MySQL → Free plan**, then pick a region (e.g. Europe). Wait until it shows **Running**.
3. Open the service **Overview → Connection information** and note:
   **Host, Port, User, Password, Database name** (`defaultdb`), and download or copy the **CA certificate**.

## 2. Create the web service (Render)

1. Sign up at **render.com** with GitHub.
2. **New → Web Service →** choose the `lasu-safety-watch` repo.
   - Language / runtime: **Docker**
   - Instance type: **Free**
   - Health check path: `/up`
3. **Environment variables** (Add from .env, then paste):

```
APP_NAME="LASU Safety Watch"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:PASTE_FROM_key:generate
APP_URL=https://YOUR-SERVICE.onrender.com
APP_TIMEZONE=Africa/Lagos
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_STORE=database
DB_CONNECTION=mysql
DB_HOST=<Aiven host>
DB_PORT=<Aiven port>
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=<Aiven password>
SEED_DEMO_REPORTS=true
```

4. Add one more variable **`MYSQL_SSL_CA_CERT`** and paste the **entire** Aiven CA certificate as its value
   (from `-----BEGIN CERTIFICATE-----` to `-----END CERTIFICATE-----`).
5. **Create Web Service.** The first build takes about 5–8 minutes. On start the app creates the tables,
   the categories, the staff accounts and the demo reports.

`APP_KEY`: run `php artisan key:generate --show` in the project folder and paste the output.

## 3. Check it

Open `https://YOUR-SERVICE.onrender.com`, then log in as `admin@lasu.test` / `Admin@1234`.
**Change the demo passwords** under Users & roles, and set `SEED_DEMO_REPORTS=false` before real use.

## Troubleshooting

| Problem | Fix |
|---|---|
| Logs show `SSL connection is required` or `Access denied` | `MYSQL_SSL_CA_CERT` is missing or incomplete, or the DB password is wrong. |
| `No application encryption key` | `APP_KEY` is missing or doesn't start with `base64:`. |
| Site takes ~1 minute to open | Normal on the free plan after it has been idle. Open it a few minutes before presenting. |
