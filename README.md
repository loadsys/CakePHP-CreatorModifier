# CakePHP 3 CreatorModifier plugin

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-CreatorModifier.svg?style=flat-square)](https://github.com/loadsys/CakePHP-CreatorModifier/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/loadsys/CakePHP-CreatorModifier.svg?branch=master&style=flat-square)](https://travis-ci.org/loadsys/CakePHP-CreatorModifier)
[![Coverage Status](https://coveralls.io/repos/loadsys/CakePHP-CreatorModifier/badge.svg)](https://coveralls.io/r/loadsys/CakePHP-CreatorModifier)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-creatormodifier.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-creatormodifier)

Sets a `creator_id` and `modifier_id` on records during save using the logged in User.id field. Operates almost identically to the core's [Timestamp behavior](http://book.cakephp.org/3.0/en/orm/behaviors/timestamp.html).


## Requirements

* PHP 5.4.16+
* CakePHP 3.0+


## Installation

### Composer

````bash
$ composer require loadsys/cakephp-creatormodifier:~1.0
````

In your `config/bootstrap.php` file, add:

```php
Plugin::load('CreatorModifier', ['bootstrap' => false, 'routes' => false]);
```

OR

```php
bin/cake plugin load CreatorModifier
```

## Usage

* Add this plugin for use in an Table, by adding this line to your Table's `initialize()` method.

````php
$this->addBehavior('CreatorModifier.CreatorModifier');
````

* Or to customize the behavior

````php
$this->addBehavior('CreatorModifier.CreatorModifier', [
	'events' => [
		'Model.beforeSave' => [
			// Field storing the User.id who created the record,
			// only triggers on beforeSave when the Entity is new.
			'user_who_created_me_id' => 'new',

			// Field storing the User.id who modified the record,
			// always triggers on beforeSave.
			'user_who_modified_me_id' => 'always'
		]
	],
	// The key to read from `\Cake\Network\Request->session()->read();`
	// to obtain the User.id value to set during saves.
	'sessionUserIdKey' => 'Auth.User.id',
]);
````


## Contributing

### Code of Conduct

This project has adopted the Contributor Covenant as its [code of conduct](CODE_OF_CONDUCT.md). All contributors are expected to adhere to this code. [Translations are available](http://contributor-covenant.org/).

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-CreatorModifier/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

Set up a working copy:
```shell
$ git clone git@github.com:YOUR_USERNAME/CakePHP-CreatorModifier.git
$ cd CakePHP-CreatorModifier/
$ composer install
$ vendor/bin/phpcs --config-set installed_paths vendor/loadsys/loadsys_codesniffer,vendor/cakephp/cakephp-codesniffer
```

Make your changes:
```shell
$ git checkout -b your-topic-branch
# (Make your changes. Write some tests.)
$ vendor/bin/phpunit
$ vendor/bin/phpcs -p --extensions=php --standard=Loadsys ./src ./tests
```

Then commit and push your changes to your fork, and open a pull request.

## License

[MIT](https://github.com/loadsys/CakePHP-CreatorModifier/blob/master/LICENSE.md)


## Copyright

[Loadsys Web Strategies](http://www.loadsys.com) 2016
