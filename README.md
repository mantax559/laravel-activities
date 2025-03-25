![GitHub release (latest by date)](https://img.shields.io/github/v/release/mantax559/laravel-activities?label=latest&style=flat-square)
![GitHub release (latest SemVer including pre-releases)](https://img.shields.io/github/v/release/mantax559/laravel-activities?include_prereleases&label=pre-release&style=flat-square)
![Packagist](https://img.shields.io/packagist/l/mantax559/laravel-activities?style=flat-square)
![PHP from Packagist](https://img.shields.io/packagist/php-v/mantax559/laravel-activities?style=flat-square)
# Laravel Activities
## Installation & Setup
You can install the package via composer:

    composer require mantax559/laravel-activities

The package will automatically register its service provider.

## Customisation

### Config

You can optionally publish the config file with:

    php artisan vendor:publish --provider="Mantax559\LaravelActivities\Providers\AppServiceProvider" --tag=config

### Tests
You can run tests with the command:

    vendor/bin/phpunit

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
