# laravel-scout-elasticsearch

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Add [elasticsearch](elastic.co/guide/index.html) engine to [Laravel Scout](https://laravel.com/docs/5.5/scout)

# Under development. Not ready for production.

## Install

Via Composer

``` bash
$ composer require alhoqbani/laravel-scout-elasticsearch
```

## Usage

### Quick Start

Publish scout config file and change the driver to `elastic`
```php
    'driver' => env('SCOUT_DRIVER', 'elastic'),
``` 
Publish the config file for this library:
```bash
php artisan vendor:publish --provider "Alhoqbani\Elastic\ScoutElasticServiceProvider"
```
update the configuration for elasticsearch hosts. 

Add `Laravel\Scout\Searchable` trait to your model
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;
    //
}
```

- Run `php artisan scout:import "App\Post"` to import all records to elasticsearch
- Search `$posts = App\Post::search('Star Trek')->get();`

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email h.alhoqbani@gmail.com instead of using the issue tracker.

## Credits

- [Hamoud Alhoqbani][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/alhoqbani/laravel-scout-elasticsearch.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/alhoqbani/laravel-scout-elasticsearch/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/alhoqbani/laravel-scout-elasticsearch.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/alhoqbani/laravel-scout-elasticsearch.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/alhoqbani/laravel-scout-elasticsearch.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/alhoqbani/laravel-scout-elasticsearch
[link-travis]: https://travis-ci.org/alhoqbani/laravel-scout-elasticsearch
[link-scrutinizer]: https://scrutinizer-ci.com/g/alhoqbani/laravel-scout-elasticsearch/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/alhoqbani/laravel-scout-elasticsearch
[link-downloads]: https://packagist.org/packages/alhoqbani/laravel-scout-elasticsearch
[link-author]: https://github.com/alhoqbani
[link-contributors]: ../../contributors
