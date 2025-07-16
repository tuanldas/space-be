# Space BE

## Setup Instructions

### Development Environment

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/space-be.git
   cd space-be
   ```

2. **Set up environment variables**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` file with your development configuration.

   **Important for Docker environments**: Set the `OAUTH_SERVER_URL` to use the internal service name:
   ```
   OAUTH_SERVER_URL=http://nginx
   ```

3. **Start Docker containers**

   ```bash
   docker network create app-network
   docker volume create --name=${VOLUME_DB_LIB:-space_be_db_data}
   docker compose -f compose.yml -f compose-local.yml up -d
   ```

4. **Install dependencies**

   ```bash
   docker compose exec php-fpm composer install
   ```

5. **Generate application key**

   ```bash
   docker compose exec php-fpm php artisan key:generate
   ```

6. **Run migrations**

   ```bash
   docker compose exec php-fpm php artisan migrate
   ```

7. **Install Passport**

   ```bash
   docker compose exec php-fpm php artisan passport:install
   ```

8. **Access the application**
   
   The application should now be running at http://localhost:8089

### Production Environment

1. **Clone the repository on your production server**

   ```bash
   git clone https://github.com/yourusername/space-be.git
   cd space-be
   ```

2. **Set up environment variables**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` file with your production configuration including:
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure your database credentials
   - Set appropriate cache and queue drivers
   - Set `OAUTH_SERVER_URL=http://nginx` for internal Docker service communication

3. **Create required networks and volumes**

   ```bash
   docker network create publish
   docker volume create --name=${VOLUME_DB_LIB:-space_be_db_data}
   ```

4. **Build and start the production containers**

   ```bash
   docker compose -f compose.yml -f compose-prod.yml up -d
   ```

5. **Install dependencies**

   ```bash
   docker compose exec php-fpm composer install --no-dev --optimize-autoloader
   ```

6. **Generate application key**

   ```bash
   docker compose exec php-fpm php artisan key:generate
   ```

7. **Run migrations**

   ```bash
   docker compose exec php-fpm php artisan migrate --force
   ```

8. **Install Passport**

   ```bash
   docker compose exec php-fpm php artisan passport:install
   ```

9. **Set proper permissions**

   ```bash
   docker compose exec php-fpm chown -R www-data:www-data storage bootstrap/cache
   ```

10. **Cache configuration and routes for better performance**

    ```bash
    docker compose exec php-fpm php artisan config:cache
    docker compose exec php-fpm php artisan route:cache
    ```

### Testing Environment

To run the test suite:

```bash
docker compose -f compose.yml -f compose.test.yml up -d
docker compose exec php-fpm composer test
```

Or run specific test suites:

```bash
docker compose exec php-fpm composer test:unit
docker compose exec php-fpm composer test:feature
```

## API Documentation

### Postman Collection

A Postman collection is available in the project. To import it:

1. Download or copy the file at `public/postman_collection.json`
2. Open Postman
3. Click on "Import" in the top left corner
4. Choose the downloaded file or paste the JSON content
5. Set up an environment in Postman with the variable `base_url` pointing to your API URL (default: `http://localhost`)

The collection includes all available API endpoints with example requests.

### OAuth 2.0 Authentication Flow

This API uses OAuth 2.0 Password Grant flow for authentication. Here's how it works:

1. **Registration**: Create a new user
2. **Login**: Exchange credentials for tokens
3. **Access Resources**: Use access token to access protected resources
4. **Refresh Token**: Use refresh token to get new access token when expired
5. **Logout**: Revoke tokens when done

## Configuration Notes

### OAuth in Docker Environment

When running the application in Docker containers, the OAuth adapter needs to communicate with the OAuth server using the internal Docker service name rather than localhost or the external URL.

- In your `.env` file, set `OAUTH_SERVER_URL=http://nginx` to ensure proper communication between services.
- This setting is separate from your `APP_URL` which should be set to the external URL for the application.

## Maintenance

### Updating Dependencies

```bash
docker compose exec php-fpm composer update
```

### Running Database Migrations

```bash
docker compose exec php-fpm php artisan migrate
```

### Clearing Cache

```bash
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan route:clear
```
