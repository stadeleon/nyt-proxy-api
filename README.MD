# NYT Bestsellers API Proxy

This project is a Laravel-based API proxy that wraps around the
[New York Times Best Sellers API](
https://developer.nytimes.com/docs/books-product/1/routes/lists/best-sellers/history.json/get
). It allows querying bestseller books with filtering options and provides caching for optimized performance.

## Features
- Fetch NYT Bestsellers with filters (ISBN, author, title, offset)
- Supports multiple ISBNs in a single request
- Implements caching using Redis for improved performance
- Provides OpenAPI documentation via Swagger
- Tested with unit, feature, and external tests

## Services in Docker
The application runs using `docker-compose` and consists of the following services:
- **PHP-FPM** (Laravel API backend)
- **Nginx** (Web server to serve the API)
- **Redis** (Cache layer for API responses)

## Installation & Setup
### 1. Start the Application
Ensure you have [Docker](https://www.docker.com/) and 
[Docker Compose](https://docs.docker.com/compose/) installed, then run:

```sh
docker-compose up -d --build
```

This will start the necessary containers.

### 2. Configure Environment
Create the `.env` file by copying `.env.example` if it does not exist.
***Ensure that it is located in the project's`root directory`***, as it `will be mounted` into the `app` 
container when starting the application. it contains the following settings:

```ini
APP_ENV=local
APP_KEY=your-app-key
NYT_API_KEY=your-nyt-api-key
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

For testing, ensure that the **`.env.testing`** file inside the Laravel framework directory is properly configured:

```ini
APP_ENV=testing
CACHE_DRIVER=array
NYT_API_KEY=test-api-key
```

### API Documentation
Swagger UI is available at:

```
http://localhost/api/docs
```

## Usage
Once the containers are running, you can access the API via:

### Example Requests
#### Query by Multiple ISBNs
```
GET http://localhost/api/v1/nyt-bestsellers?isbn[]=9781451627282&isbn[]=9780399169274
```
#### Query by Author and Title
```
GET http://localhost/api/v1/nyt-bestsellers?author=Stephen+King&title=The+Shining
```

### Available Query Parameters
- `isbn[]` (array of strings): Filter by one or more ISBNs
- `author` (string): Filter by author name
- `title` (string): Filter by book title
- `offset` (integer): Page offset for pagination

```
http://localhost/api/v1/nyt-bestsellers
```

## Running Tests
### 1. Unit & Feature Tests
To execute unit and feature tests:

```sh
docker-compose exec app php artisan test
```

### 2. External API Tests
To run tests that interact with the real NYT API:

```sh
docker-compose exec app php artisan test --group external
```

### 3. Test Coverage Report
Code coverage has already been generated. Note that the `coverage/` directory is typically added to `.gitignore` to avoid committing large reports.
To generate a code coverage report:

```sh
docker-compose exec app XDEBUG_MODE=coverage php artisan test --coverage-html coverage
```

The generated coverage report can be viewed at:

```
app/coverage/index.html
```
