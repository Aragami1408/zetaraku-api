# Zetaraku API

This is a non-production-grade REST API project from https://arcade-songs.zetaraku.dev/maimai/.

Please follow these steps to setup the API, make sure to have `docker` and `docker-compose` installed:

1. Create an `.env` file in project's root with this format for DB credentials:
```
MYSQL_USER={your_name}
MYSQL_DATABASE={db_name}
MYSQL_PASSWORD={very_secret_password}
MYSQL_ROOT_PASSWORD={even_more_secret_password}
```
2. Create `./src/zetaraku/config.php` with this format:
```
<?php
// Adjust to your environment
$DB_HOST = '';
$DB_NAME = '';
$DB_USER = '';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';
?>
```
3. Startup all containers with `docker-compose up -d`
4. Navigate to `localhost:8080` to open PHPMyAdmin dashboard then insert [this db setup query](./src/zetaraku/setup.sql) to setup the database and insert sample data
5. You can finally use the API with `localhost:8000/zetaraku/index.php`

Have fun!
