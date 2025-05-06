# nyt-api-demo
Demo project for NYT Bestsellers API

### Build Application

```shell
docker-compose up -d
```

Image only

```shell
docker build -f Dockerfile -t nyt-demo/php:dev .
```

## Make API calls

When launched, the API would be available on `GET /api/v1/best-sellers` endpoint. It is protected by Bearer token.
To get the token, login first via `POST /api/v1/login`. The demo app seeds DB with a default `nyt@demo.com:demo` user.
To make real API calls make sure to set `NYT_API_KEY` environment variable.

## Tests

### Unit Tests

```shell
docker-compose exec app php artisan test --testsuite=Unit
```

Run unit tests only without real API calls.

### Feature Tests

```shell
docker-compose exec app php artisan test --testsuite=Feature
```

Feature suite contains tests which make real API calls using the app API URL as proxy.
For this to work make sure to set `NYT_API_KEY` environment variable in the `.env.testing`

## Caching

New York Times Bestsellers list is updated weekly, so by default the response is also cached for a week.

## Static Analysis

Some static analysis tools have been configured for the application.

### PHPStan
```shell
docker-compose exec app vendor/bin/phpstan analyse --memory-limit 1G -c phpstan.neon
```
or
```shell
docker-compose exec app composer phpstan
```

### PHPCS
```shell
docker-compose exec app vendor/bin/phpcs --standard=phpcs.xml
```
or
```shell
docker-compose exec app composer phpcs
```

### PHPMD
```shell
docker-compose exec app vendor/bin/phpmd app ansi phpmd.xml
```
or
```shell
docker-compose exec app composer phpmd
```
