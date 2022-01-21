# Laravel Challenge

A demo package for Laravel to show code challenges

## Installation

``` bash
composer require dalyio/laravel-challenge
```

``` bash
php artisan challenge:install
```

## Populate Data

Populate number chain data with this artisan command.  The `-e` option is to specify a number to populate data through.

``` bash
php artisan challenge:numberchain -e 100000
```

Populate number zipcode data with this artisan command.  Specify the filename to use in the storage directory.

``` bash
php artisan challenge:zipcode zipcodes.txt
```

## License

Laravel Challenge is open-sourced software licensed under the [MIT license](LICENSE).
