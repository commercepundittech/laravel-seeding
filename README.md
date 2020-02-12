# laravel-seeding
This is simply laravel seeding module for database.

### How to install

~~~bash
composer require commercepundittech/laravel-seeding
~~~

### How to activate this module in Laravel application

Add the ServiceProvider to the providers array in `config/app.php`

```php
CommercePunditTech\LaravelSeeding\LaravelSeedingServiceProvider,
```

Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --provider="CommercePunditTech\LaravelSeeding\LaravelSeedingServiceProvider"
```

### TO Do List

- [x] Add feature like Laravel migration for Database seeding
