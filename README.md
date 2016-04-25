# validation

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Code Climate][ico-cc]][link-cc]
[![Tests Coverage][ico-cc-coverage]][link-cc]

An extended implementation of the routing class, dealing with an HTTP abstraction and middleware.

## Install

Via Composer

``` bash
$ composer require vakata/validation
```

## Usage

``` php
$v = new \vakata\validation\Validator();
$v
    ->required('name', 'requiredN')->alpha(null, "alphaN")->notEmpty("empty")
    ->required('family', 'requiredF')->alpha(null, "alphaF")
    ->required('age', 'requiredA')->numeric("numericA")
    ->optional("newsletter")->numeric("numericN")
    ->optional("children.*.name")->alpha(null, "alphaC")
    ->optional("children.*.age")->numeric(null, "numericC");
$errors = $v->run($_POST);
// inspect the array - if empty - the data is valid
```

Read more in the [API docs](docs/README.md)

## Testing

``` bash
$ composer test
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email github@vakata.com instead of using the issue tracker.

## Credits

- [vakata][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/vakata/validation.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/vakata/validation/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/vakata/validation.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/vakata/validation.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/vakata/validation.svg?style=flat-square
[ico-cc]: https://img.shields.io/codeclimate/github/vakata/validation.svg?style=flat-square
[ico-cc-coverage]: https://img.shields.io/codeclimate/coverage/github/vakata/validation.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/vakata/validation
[link-travis]: https://travis-ci.org/vakata/validation
[link-scrutinizer]: https://scrutinizer-ci.com/g/vakata/validation/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/vakata/validation
[link-downloads]: https://packagist.org/packages/vakata/validation
[link-author]: https://github.com/vakata
[link-contributors]: ../../contributors
[link-cc]: https://codeclimate.com/github/vakata/validation

