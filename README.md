

## Использованные инструменты

- Laravel
- Docker
- PostgreSQL
- JWT (JSON Web Tokens)
- PHPUnit
- Swagger

## Установка и запуск


### Шаг 1: Запуск Docker контейнеров
docker-compose up --build

### Шаг 2: Запуск Docker контейнеров
docker exec -it myapp bash

	Установка

    cp .env.example .env и заполнить необходимыми данными
    composer install
    php artisan migrate
    php artisan key:generate
    php artisan jwt:secret
