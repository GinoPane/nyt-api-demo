# nyt-api-demo
Demo project for NYT Bestseller's API

### Build Application Image

```shell
docker build -f Dockerfile -t nyt-demo/php:dev .
```

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
