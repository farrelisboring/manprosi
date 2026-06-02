# Deployment Notes

## Vercel + Railway MySQL

This project is prepared for GitHub-based deployment to Vercel using the `vercel-php` runtime and a Railway MySQL database.

### 1. Import the repository into Vercel

1. Push this repository to GitHub.
2. In Vercel, create a new project from that GitHub repository.
3. Keep the project root at the repository root.

Vercel will use:

- `vercel.json` for the PHP entrypoint and routing
- `composer run vercel` during build, which installs Node dependencies, builds Vite assets, and creates the storage symlink

### 2. Set these Vercel environment variables

Minimum recommended variables:

```text
APP_NAME=Hospital Asset Manager
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-vercel-domain.vercel.app
SHORT_LINK_APPEND=https://your-vercel-domain.vercel.app

DB_CONNECTION=mysql
DB_URL=mysql://root:...@roundhouse.proxy.rlwy.net:25539/railway

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stderr
```

### 3. Railway variable mapping

For Vercel, use Railway's public database connection, not the internal one.

Recommended:

- `DB_URL` = value of Railway `MYSQL_PUBLIC_URL`

This project also understands Railway-style variables directly:

- `MYSQL_PUBLIC_URL`
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

So if you prefer, you can set those instead of `DB_URL`, but `DB_URL` is the simplest option.

### 4. Run migrations against Railway

Migrations are not run automatically during Vercel deploys.

Run them manually against the Railway database before using the deployed app:

```powershell
& 'C:\Users\Farrel\AppData\Local\Programs\PHP\current\php.exe' artisan migrate --force
```

If you also want the seeded role users:

```powershell
& 'C:\Users\Farrel\AppData\Local\Programs\PHP\current\php.exe' artisan db:seed --force
```

Make sure your local `.env` points to the Railway MySQL database before running those commands.

### 5. Important limitation: uploaded files

`Denah` uploads currently use Laravel's local `public` disk.

That works locally, but Vercel's filesystem is not durable for user uploads. In practice that means:

- new uploads on Vercel will not be reliable long-term
- uploaded files should eventually move to external object storage such as S3 / Cloudflare R2 / Vercel Blob

The database-backed parts of the app are fine on Vercel + Railway. The current weak spot is persistent uploaded media.
