# Free deployment: Render (app) + TiDB Cloud (MySQL)

This is how the live site is deployed. Everything is free and needs no card:

| Part | Service | Free plan |
|---|---|---|
| Web app | **Render** web service, built from this repo's `Dockerfile` | Sleeps after 15 min idle; the first visit after that takes ~1 minute |
| Database | **TiDB Cloud Starter** (MySQL-compatible) | Monthly free allowance; spending limit set to $0 so it can never charge |

> Uploaded photos are stored on Render's temporary disk and are lost when the app restarts or redeploys.
> For a defence or demo this is fine. For long-term use, move to a paid plan with a persistent disk (e.g. Railway, see `DEPLOY-RAILWAY.md`).

## 1. Database (TiDB Cloud)

1. Sign up at **tidbcloud.com** (GitHub or Google).
2. **Create Resource → Starter**, Compatibility **MySQL**, region **Frankfurt (eu-central-1)**, spending limit **$0**.
3. Open the instance → **Connect** → **Generate Password**. Note the **host**, **port** (4000), **username** and **password**.
4. Create the app database once (any MySQL client, or TiDB's SQL Editor): `CREATE DATABASE lasu_safety;`

TiDB requires an encrypted (TLS) connection. Its certificate is signed by a public authority, so the server's
built-in bundle `/etc/ssl/certs/ca-certificates.crt` is enough. No certificate needs to be pasted.

## 2. Web service (Render)

1. Sign up at **render.com** with GitHub.
2. **New → Web Service →** `lasu-safety-watch` repo. Language **Docker**, region **Frankfurt**, instance type **Free**.
3. **Advanced → Health Check Path:** `/up`
4. **Environment variables** (*Add from .env*):

```
APP_NAME=LASU Safety Watch
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # php artisan key:generate --show
APP_URL=https://lasu-safety-watch.onrender.com
APP_TIMEZONE=Africa/Lagos
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_STORE=database
DB_CONNECTION=mysql
DB_HOST=gateway01.eu-central-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=lasu_safety
DB_USERNAME=<TiDB username, ends in .root>
DB_PASSWORD=<TiDB password>
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
SEED_DEMO_REPORTS=true
```

5. **Deploy web service.** On start, the container runs the migrations and creates the categories,
   staff accounts and (with `SEED_DEMO_REPORTS=true`) 20 demo reports.

Every `git push` to `main` redeploys automatically.

## 3. Before real use

- Log in as `admin@lasu.test` / `Admin@1234`, then change **every** demo password under *Users & roles*.
- Set `SEED_DEMO_REPORTS=false`.
- Put the real LASU phone numbers and landmark coordinates in `config/lsw.php`.

## Troubleshooting

| Problem | Fix |
|---|---|
| Logs show `Connections using insecure transport are prohibited` | `MYSQL_ATTR_SSL_CA` is missing. |
| `Access denied` | Wrong `DB_USERNAME` (it includes a prefix such as `xxxx.root`) or password. Regenerate it in TiDB → Connect. |
| `Unknown database 'lasu_safety'` | Run `CREATE DATABASE lasu_safety;` in TiDB's SQL Editor. |
| Site takes ~1 minute to open | Normal on the free plan after it has been idle. Open it a few minutes before presenting. |
