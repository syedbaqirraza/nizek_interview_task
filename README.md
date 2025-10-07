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

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=stock_db
DB_USERNAME=root
DB_PASSWORD=root

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
docker exec -it <app_container_name> php artisan migrate
docker exec -it <app_container_name> php artisan db:seed --class=CompanySeeder
```

5. Start the queue worker inside the same container (so it can reach the `mysql` service):

```powershell
docker exec -d <app_container_name> php artisan queue:work --tries=3
```

6. Start the schdule run worker inside the same container:

```powershell
docker exec -d <app_container_name> php artisan schedule:run
```

Notes:
- Replace `<app_container_name>` with the actual container name (from `docker ps`). Running the worker inside the same Docker network ensures service hostnames like `mysql` resolve correctly.

### Upload page and API endpoints

- Upload form (Livewire): `/` — use this page to upload the Excel/CSV file for a company. The file is processed by a queued job which imports prices into `stock_prices`.

- API to get period change:

  `GET /api/stocks/{company}/changes?period=1M`

  Valid `period` values: `1D,1M,3M,6M,YTD,1Y,3Y,5Y,10Y,MAX`.

- API to compare custom dates:

  `GET /api/stocks/{company}/compare?start_date=2024-01-01&end_date=2024-10-01`

  Both `start_date` and `end_date` must be valid dates and `end_date` must be same or after `start_date`.

