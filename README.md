# CreatorModifier

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-CreatorModifier.svg?style=flat-square)](https://github.com/loadsys/CakePHP-CreatorModifier/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/loadsys/CakePHP-CreatorModifier.svg?branch=master&style=flat-square)](https://travis-ci.org/loadsys/CakePHP-SocialLinks)
[![Coverage Status](https://coveralls.io/repos/loadsys/CakePHP-CreatorModifier/badge.svg)](https://coveralls.io/r/loadsys/CakePHP-CreatorModifier)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-creatormodifier.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-creatormodifier)

Sets a `creator_id` and `modifier_id` using the logged in User.id field.

## Requirements

* PHP 5.4.16+
* CakePHP 3.0+

## Installation

### Composer

````bash
$ composer require loadsys/loadsys/cakephp-creatormodifier:~1.0
````

## Usage ##

* Add this plugin for use in an Table, by adding this line to your Table's `initialize` method.

````php
$this->addBehavior('CreatorModifier.CreatorModifier');
````

* Or to customize the behavior

````php
$this->addBehavior('CreatorModifier.CreatorModifier', [
	'events' => [
		'Model.beforeSave' => [
			'user_who_created_me_id' => 'new', // field of the User.id who created, only change on beforeSave when is a new Entity.
			'user_who_modified_me_id' => 'always' // field of the User.id who modified, always change on beforeSave.
		]
	],
	'sessionUserIdKey' => 'Auth.User.id', // The key to read from `\Cake\Network\Request->session()->read('Auth.User.id');` for the User.id value to set.
]);
````

## Contributing

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-CreatorModifier/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

## License ##

[MIT](https://github.com/loadsys/CakePHP-CreatorModifier/blob/master/LICENSE.md)


## Copyright ##

[Loadsys Web Strategies](http://www.loadsys.com) 2015
