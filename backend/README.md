# Laravel
## Get started
# FrontApi

### Install packages

Install the packages described in the `composer.json` and verify that it works:

```shell
composer install
```

#### Make sure to give read / write / execute permission to storage folder

```shell
chmod -r 775 storage/
```

#### Clear the confiuration and generate key

```shell
php artisan config:clear
php artisan key:generate
php artisan config:clear
```

