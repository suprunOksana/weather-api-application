# Weather Subscription API

This is a simple REST API built with PHP and Slim Framework that allows users to:

* Get current weather information for a specified city using the [weatherapi.com](https://www.weatherapi.com/) service.
* Subscribe to weather updates by providing email, city, and frequency (hourly or daily).
* Confirm subscription via a confirmation email with a token.
* Unsubscribe from updates via a token link.

## Features

* Slim Framework based REST API
* PostgreSQL for storing subscription data
* PHPMailer for sending confirmation emails
* dotenv for environment variable management
* PHPUnit tests included
* Dockerized setup for easy local development and testing

## Project Structure

* /src — source code (controllers, models, routes)
* /tests — PHPUnit tests
* /migrations — database migrations
* docker-compose.yml — Docker setup
* phpunit.xml — PHPUnit configuration
* .env.example — example environment variables

## Prerequisites

* A WeatherAPI key (https://www.weatherapi.com/)
* SMTP credentials for sending emails (e.g., Gmail SMTP or other)


## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/suprunOksana/weather-api-application.git
cd weather-api-application
```

### 2. Create .env file

Copy .env.example to .env and fill in your credentials

### 3. Start Docker containers

```bash
docker compose up -d
```

### 4. Run database migrations

```bash
docker compose run --rm app php migrations.php
```

### 5. Access the API
The API will be available at http://localhost:8080.

Example endpoints:

* GET /api/weather?city={city}
* POST /api/subscribe 
* GET /api/confirm/{token}
* GET /api/unsubscribe/{token}

### 6. Run tests

```bash
docker compose run --rm tests vendor/bin/phpunit tests
```
