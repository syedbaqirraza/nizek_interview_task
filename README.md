# Laravel Stock App

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About this project

This repository contains a Laravel application that allows uploading historical stock prices for companies (via a Livewire upload form) and exposes APIs to compute value and percentage changes over common periods (1D, 1M, 3M, 6M, YTD, 1Y, 3Y, 5Y, 10Y, MAX) as well as between two custom dates.

Key features implemented:
- Livewire upload form at `GET /upload` that stores an uploaded Excel/CSV in `storage/app/temp` and queues a job to import into `stock_prices`.
- API endpoints:
  - `GET /api/stocks/{company}/changes?period=1M` — returns current and previous price and percentage change for the requested period.
  - `GET /api/stocks/{company}/compare?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD` — compares prices between two dates.
- Imports use Laravel Excel with chunking and batch inserts for performance.

## How to launch this application

This project can be launched using Docker. Below are concise steps for environment, plus how to run the queue worker, run migrations/seeds, and test the upload/API endpoints.


### Using Docker

1. Copy the environment file and adjust values if needed:

```powershell
copy .env.example .env
```

2. Build and start containers (from project root):

```powershell
docker-compose up -d --build
```

3. Install PHP dependencies and generate key inside the app container (if not baked into the image):

```powershell
# find container name from `docker ps` and replace <app_container_name>
docker exec -it <app_container_name> composer install --no-interaction --prefer-dist
docker exec -it <app_container_name> php artisan key:generate
```

4. Run migrations and seed (inside container):

```powershell
docker exec -it <app_container_name> php artisan migrate --seed --force
```

5. (Optional) Build frontend assets inside the container:

```powershell
docker exec -it <app_container_name> npm ci
docker exec -it <app_container_name> npm run build
```

6. Start the queue worker inside the same container (so it can reach the `mysql` service):

```powershell
docker exec -d <app_container_name> php artisan queue:work --tries=3
```

Notes:
- Replace `<app_container_name>` with the actual container name (from `docker ps`). Running the worker inside the same Docker network ensures service hostnames like `mysql` resolve correctly.

### Local (XAMPP) setup on Windows

1. Copy the environment file:

```powershell
copy .env.example .env
```

2. Edit `.env` and make sure database connection values point to a reachable DB.

3. Install dependencies (Windows cmd / PowerShell):

```powershell
composer install
npm ci
php artisan key:generate
```

4. Run migrations and seed:

```powershell
php artisan migrate --seed
```

5. Create storage link and build assets:

```powershell
php artisan storage:link
npm run build
```

6. Run queue worker (if running jobs locally):

```powershell
php artisan queue:work --tries=3
```

Note: If you run the worker on your host and the DB is in Docker, ensure `.env` DB_HOST is reachable from the host (e.g., map container port to host or use host networking). The typical failure mode is `php_network_getaddresses: getaddrinfo for mysql failed` when the worker cannot resolve the DB host name.

### Upload page and API endpoints

- Upload form (Livewire): `GET /upload` — use this page to upload the Excel/CSV file for a company. The file is processed by a queued job which imports prices into `stock_prices`.

- API to get period change:

  `GET /api/stocks/{company}/changes?period=1M`

  Valid `period` values: `1D,1M,3M,6M,YTD,1Y,3Y,5Y,10Y,MAX`.

- API to compare custom dates:

  `GET /api/stocks/{company}/compare?start_date=2024-01-01&end_date=2024-10-01`

  Both `start_date` and `end_date` must be valid dates and `end_date` must be same or after `start_date`.

### Testing an import synchronously (quick)

If you want to run an import immediately (helpful for debugging), run this in your app environment:

```powershell
php artisan tinker
>>> \App\Jobs\ProcessStockFile::dispatchSync('temp/your_file.xlsx', 1);
```

Replace `temp/your_file.xlsx` with the actual temp path under `storage/app/temp` and `1` with the company id.

### Activating the scheduler (run scheduled tasks)

The app includes a scheduler (`app/Console/Kernel.php`) that runs two scheduled tasks:
- `cleanup:livewire-tmp` daily at 00:00 to remove old temporary files
- `queue:work --once --tries=3` every minute to process queued jobs safely

To trigger the Laravel scheduler, you must run `php artisan schedule:run` every minute. Below are options to do that depending on your environment:

- Linux / Docker host (recommended): add a cron entry for the host that calls the artisan command inside the app container:

```cron
* * * * * docker exec <app_container_name> php /var/www/html/artisan schedule:run >> /dev/null 2>&1
```

- Direct cron on server (if PHP is installed on host):

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

- Windows Task Scheduler (XAMPP / local development): create a task that runs every minute with these settings:
  - Program/script: `C:\path\to\php.exe`
  - Arguments: `C:\path\to\project\artisan schedule:run`
  - Start in: `C:\path\to\project`

Notes:
- Running the scheduler every minute allows Laravel to trigger the short-lived queue worker scheduled in the Kernel. If you prefer a long-running worker, run `php artisan queue:work` as a service instead and do not schedule the `queue:work --once` command.

If you want, I can add an example `docker-compose` service entry to run the scheduler inside Docker, or generate an example Windows Task Scheduler export (.xml) for you.


