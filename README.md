[![Code Coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/henryfoster/8be2c25f29d9a26195c4210df1db0267/raw/coverage.json)](https://github.com/henryfoster/shopping-cart)

# shopping-cart

## Instructions

### Setup
1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Load Fixtures: `docker compose exec -T php bin/console do:fi:lo --no-interaction`
5. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Api Docs can be found on `https://localhost/api/doc`
7.  Run `docker compose down --remove-orphans` to stop the Docker containers.

### Quick Commands
## Run server
### With https
```bash
docker compose up -d
```

### With http
```bash
SERVER_NAME=:80 docker compose up -d
```

### With XDebug enabled
```bash
XDEBUG_MODE=debug docker compose up -d
```

## Run Tests
```bash
docker compose exec -T php bin/console -e test doctrine:database:create
docker compose exec -T php bin/console -e test doctrine:migrations:migrate --no-interaction
docker compose exec -T php bin/phpunit
```

## Generate Test-Coverage report
### HTML
```bash
docker compose exec -e XDEBUG_MODE=coverage -T php bin/phpunit --coverage-html ./coverage-report
```
### XML
```bash
docker compose exec -e XDEBUG_MODE=coverage -T php bin/phpunit --coverage-clover clover.xml
```

# Run PHPStan
```bash
docker compose exec -T php vendor/bin/phpstan analyse src --memory-limit=-1
```

# Run PHP-CS-Fixer
```bash
docker compose exec -T -e PHP_CS_FIXER_IGNORE_ENV=true php ./vendor/bin/php-cs-fixer fix src --dry-run
```

## Bootstraped with dunglas/symfony-docker Template
[Symfony Docker Repo](https://github.com/dunglas/symfony-docker)

Read the docs: [FrankenPHP Symfony template Docs](docs/README.md)
