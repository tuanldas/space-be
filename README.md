# Space BE

## Chuẩn bị môi trường

```bash
cp .env.example .env
cp compose-prod.yml compose.override.yml # prod, local
docker network create ${PUBLISH_NETWORK}
docker volume create ${VOLUME_DB_LIB}
```

## Build Docker images và containers

```bash
docker compose build
```

## Bật các services

```bash
docker compose up -d
```

## Thiết lập môi trường PHP

```bash
docker compose exec php-fpm composer update
docker compose exec php-fpm composer dump-autoload
docker compose exec php-fpm chmod -R 777 storage
docker compose exec php-fpm chmod -R 775 bootstrap/cache
```

## Cấu hình Passport

Tạo client password grant

```bash
docker compose exec php-fpm php artisan passport:keys
docker compose exec php-fpm php artisan passport:client --password
```

Cập nhật env

```bash
PASSPORT_APP_URL=${APP_URL}
PASSPORT_PASSWORD_GRANT_CLIENT_ID=${CLIENT_ID}
PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=${CLIENT_SECRET}
```

## Compile static contents

```bash
docker compose exec php-fpm npm i
docker compose exec php-fpm npm run dev # local
docker compose exec php-fpm npm run build # production
```

## Chạy migration và seeding

```bash
docker compose exec php-fpm php artisan migrate
docker compose exec php-fpm composer dump-autoload
docker compose exec php-fpm php artisan db:seed
```

## Truy cập và kiểm tra

```bash
http://localhost:8080
```
