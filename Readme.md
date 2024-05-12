# 🐳 Docker + PHP 8.3 + MySQL + Nginx + Symfony 7 Boilerplate

## Description

This is a complete stack for running Symfony 7.0 in Docker containers using docker-compose tool.

Inspired by [Boilerplate para Symfony basado en Docker, NGINX y PHP8](https://youtu.be/A82-hry3Zvw)

It is composed by 3 containers:

- `nginx`, acting as the webserver.
- `php`, the PHP-FPM container with the 8.3 version of PHP.
- `db`, MySQL database container with a MySQL 8.0 image.

This project follows Hexagonal architecture principles. 

All controller entry points are located in `src/EntryPoint/Http/Controllers` folder.

We have two modules:
- `Shared` - classes designed for general use.
- `User` - user and authentication related classes.
- `SpaService` - spa services related classes.

This is a pure **API** application, i.e. you need a compatible UI app to make API requests and test the backend API in real life. Another option is to Open API doc (see below) "try it out" feature.

The API is designed to act as a mobile application backend, providing user authentication via auth tokens (similar to Laravel Sanctum auth tokens). After each successful registration and login a new token is provided in response. Client app should store it in secure area and use it for all subsequent requests.

**Main actions:**
- User can register. If registration is successful user is automatically logged in on a device used for registration.
- User can log in on multiple devices. Each registered device provides its own token that can be used to access protected pages.
- User can log out from a given device.
- User can log out from all devices.
- User can change password.
- User can delete his account.

## Installation

1. Clone this repo.
2. Go inside `./docker` folder and run `docker compose up -d` to start containers.

**Next commands should be executed inside `php` container.**

3. `docker exec -it php bash` or use your favourite Docker desktop application `php` container Exec tab.
4. Install dependencies: `#composer install`
5. Migrate database: `#php bin/console doctrine:migrations:migrate` (optional - no needed if you are going to execute PHP Unit tests **only**)
6. Run tests : `#php ./vendor/bin/phpunit`. Current project setup uses in-memory Sqlite database for testing, so migrations are done automatically before each test.
7. Browse Open API docs: http://localhost/api/doc


## What's next

You can change the name, user and password of the database in the `env` file at the root of the project. Make sure that you update `.docker/.env` settings accordingly.

.env:
```
DATABASE_URL=mysql://app_user:secret@db:3306/symfony?serverVersion=8.0.33
```
.docker/.env:
```
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=symfony
MYSQL_USER=app_user
MYSQL_PASSWORD=secret
```

Make sure that you rebuild containers after database setting are changed. In local project folder cd to `.docker`, then:
- `docker compose down --remove-orphans`
- `docker compose build --no-cache` (optional, just to make sure we have fresh images)
- `docker compose up -d`






