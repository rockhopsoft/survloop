
# WikiWorldOrder/SurvLoop

[![Laravel](https://img.shields.io/badge/Laravel-5.7-orange.svg?style=flat-square)](http://laravel.com)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

SurvLoop is a Laravel-based engine for websites dominated by the collection and publication of open data. 
This is a database design and survey generation system, though it will increasingly be a flexible tool to solve many 
web-based problems.

It is currently in continued, heavy development, with much happening here in mid-2018, almost ready to go live. 
I plan to provide more documentation in the coming weeks. Thank you for your interest and patience!

This was originally developed to build the 
<a href="https://github.com/flexyourrights/openpolice" target="_blank">Open Police</a> system. 
So until the SurvLoop installation processes automates everything, plus the bell & whistle options, 
please check out the Open Police package for an heavy example of how to extend SurvLoop for your custom needs. 
(Lighter examples coming online soon!-)

The upcoming Open Police web app is the best live <b>beta demo</b> of the engine's end results, 
and feedback on that project and the SurvLoop user experience can be  via the end of the submission process:<br />
<a href="https://openpolice.org/test" target="_blank">https://openpolice.org/test</a><br />
The resulting database designed using the engine, as well as the branching tree which specifies the user's experience: 
<a href="https://openpolice.org/db/OP" target="_blank">/db/OP</a><br />
<a href="https://openpolice.org/tree/complaint" target="_blank">/tree/complaint</a><br />
Among other methods, the resulting data can also be provided as 
XML included an automatically generated schema, eg.<br />
<a href="https://openpolice.org/complaint-xml-schema" target="_blank">/complaint-xml-schema</a><br />
<a href="https://openpolice.org/complaint-xml-example" target="_blank">/complaint-xml-example</a><br />
<a href="https://openpolice.org/complaint-xml-all" target="_blank">/complaint-xml-all</a>

Other projects running SurvLoop: <a href="https://powerscore.resourceinnovation.org/start/calculator" target="_blank">
Cannabis PowerScore</a> (<a href="https://github.com/resourceinnovation/cannabisscore" target="_blank">GitHub</a>), and
<a href="https://drugstory.me" target="_blank">Drug Story</a>.

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Documentation](#documentation)
* [Roadmap](#roadmap)
* [Change Logs](#change-logs)
* [Contribution Guidelines](#contribution-guidelines)
* [Reporting a Security Vulnerability](#security-help)


# <a name="requirements"></a>Requirements

* php: >=7.2.11
* <a href="https://packagist.org/packages/laravel/framework" target="_blank">laravel/framework</a>: 5.7.*

# <a name="getting-started"></a>Getting Started

The instructions below include the needed steps to install Laravel and SurvLoop.
For more on creating environments to host Laravel, you can find more instructions on
<a href="https://survloop.org/how-to-install-laravel-on-a-digital-ocean-server" target="_blank">SurvLoop.org</a>.

* Use Composer to install Laravel with default user authentication, one required package:

```
$ composer global require "laravel/installer"
$ composer create-project laravel/laravel SurvLoop "5.7.*"
$ cd SurvLoop
$ php artisan make:auth
$ php artisan vendor:publish --tag=laravel-notifications
```

* Update `composer.json` to add requirements and an easier SurvLoop reference:

```
$ nano composer.json
```

```
...
"require": {
	...
    "wikiworldorder/survloop": "0.*",
	...
},
...
"autoload": {
	...
	"psr-4": {
		...
		"SurvLoop\\": "vendor/wikiworldorder/survloop/src/",
	}
	...
},
...
```

```
$ composer update
```

* Add the package to your application service providers in `config/app.php`.

```php
...
    'name' => 'SurvLoop',
...
'providers' => [
	...
	SurvLoop\SurvLoopServiceProvider::class,
	...
],
...
'aliases' => [
	...
	'SurvLoop'	=> 'WikiWorldOrder\SurvLoop\SurvLoopFacade',
	...
],
...
```

* Swap out the SurvLoop user model in `config/auth.php`.

```php
...
'model' => App\Models\User::class,
...
```

* Update composer, publish the package migrations, etc...

```
$ php artisan vendor:publish --force
$ php artisan migrate
$ composer dump-autoload
$ php artisan db:seed --class=SurvLoopSeeder
```

* For now, to apply database design changes to the same installation you are working in, depending on your server, 
you might also need something like this...

```
$ chown -R www-data:33 app/Models
$ chown -R www-data:33 database
```

* Browse to load the style sheets, etc.. /dashboard/css-reload

# <a name="documentation"></a>Documentation

Once installed, documentation of this system's database design can be found at /dashboard/db/all. This system's 
survey design can be found at /dashboard/surv-1/map?all=1&alt=1
or publicly visible links like those above.

<a href="https://survloop.org/db/SL" target="_blank">https://survloop.org/db/SL</a>


# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release (**1.0**). It's my first time building on Laravel, or GitHub. So sorry.

* [ ] Correct all issues needed for minimum viable product, and launch initial beta sites powered by SurvLoop.
* [ ] Database design and user experience admin tools to be generated by SurvLoop itself. 
* [ ] Breaking up larger objects/classes into smaller ones
* [ ] Code commenting, learning and adopting more community norms.
* [ ] Finish migrating all raw queries to use Laravel's process.
* [ ] Add unit testing.

# <a name="change-logs"></a>Change Logs


# <a name="contribution-guidelines"></a>Contribution Guidelines

Please help educate me on best practices for sharing code in this community.
Please report any issue you find in the issues page.

# <a name="security-help"></a>Reporting a Security Vulnerability

We want to ensure that SurvLoop is a secure HTTP open data platform for everyone. 
If you've discovered a security vulnerability in the SurvLoop software or SurvLoop.org, 
we appreciate your help in disclosing it to us in a responsible manner.

Publicly disclosing a vulnerability can put the entire community at risk. 
If you've discovered a security concern, please email us at wikiworldorder *at* protonmail.com. 
We'll work with you to make sure that we understand the scope of the issue, and that we fully address your concern. 
We consider correspondence sent to wikiworldorder *at* protonmail.com our highest priority, 
and work to address any issues that arise as quickly as possible.

After a security vulnerability has been corrected, a release will be deployed as soon as possible.

