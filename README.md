# SprintHR Talent Portal

An applicant-facing profile portal built with Laravel, Inertia and React. It keeps applicant profile data separate from SprintHR Recruitment and starts every new profile with employer discovery turned off.

## Local setup

Requirements: PHP 8.4.1+, Composer, Node.js and npm.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

In a second terminal, run `php artisan serve` and open `http://127.0.0.1:8000`. The local database defaults to SQLite. For deployment, point the app at its own PostgreSQL database using `DB_CONNECTION=pgsql` and the corresponding database credentials; do not connect it to a SprintHR Recruitment database.

## Run with Docker Compose

Docker Compose is already available in the development environment. From the project root, start the full local stack:

```sh
docker compose up --build
```

Open `http://localhost:8180`. The first start installs PHP and Node dependencies into named volumes, creates an app key if needed, and runs migrations against the Compose PostgreSQL service. Vite runs on port `5175` for hot reload.

The project directory is bind-mounted into the PHP and Vite containers, so source edits appear without rebuilding the image. The `vendor` and `node_modules` directories use separate named volumes; changes to `composer.lock` or `package-lock.json` trigger dependency installation on the next container start. Rebuild only when the Dockerfile or its system packages change.

Stop the services with `docker compose down`. PostgreSQL and Redis data are kept in named volumes; `docker compose down -v` also removes those volumes and the installed dependency volumes. The database password and local auth settings in `compose.yaml` are for development only.

The starter's password-based registration and login are enabled only when `APP_ENV=local`. In other environments they return HTTP 503 until the approved Gleent Account OIDC flow is integrated. Do not enable `TALENT_LOCAL_AUTH_ENABLED` in production.

## Resume storage

Uploads accept PDF and DOCX files up to 10 MB. By default, resume uploads go through the Bucket API to the private R2 bucket configured by `BUCKET_API_URL`, `BUCKET_API_KEY`, and `BUCKET_NAME`. Keep `TALENT_RESUME_DISK=bucket-api-r2`. The owner-only download route checks the authenticated candidate before requesting a one-hour signed URL.

For local development without R2, set `TALENT_RESUME_DISK=local`. Resume scanning and extraction are not connected yet; the app records `awaiting_parser` and tells applicants to enter profile details manually.

## Current implementation boundary

- Applicant registration and profile ownership use Laravel's local development auth; production sign-in remains gated pending the OIDC contract.
- Candidate basics, skills, work history, education, resume metadata, append-only discovery consent events, invitation responses and deletion requests are persisted in the Talent Portal database.
- Candidate endpoints are behind Laravel authentication and scope each update to the signed-in candidate. Resume downloads are served through the app after an ownership check.
- Web responses set basic anti-sniffing, clickjacking, referrer and permissions headers; HSTS is added on HTTPS requests.
- The internal employer discovery API, Recruitment job ownership and subscription checks, invitation creation, AI screening, credit accounting, consented identity disclosure, resume scanning and parsing, notification delivery, and deletion processing are not connected because their authoritative contracts or policies were not supplied.
- A deletion request immediately removes the profile from new discovery and is recorded as pending. The approved retention schedule and deletion worker still need to be connected.

The app is a development foundation, not a launch-ready integration. Before a pilot, connect Gleent OIDC, the security scanning/parser worker, private production storage, the reviewed consent and retention policy, and SprintHR's server-side job/entitlement/screening services.
