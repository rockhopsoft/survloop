
# WikiWorldOrder/SurvLoop

[![Laravel](https://img.shields.io/badge/Laravel-5.8-orange.svg?style=flat-square)](http://laravel.com)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

# Table of Contents
* [About](#about)
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Documentation](#documentation)
* [Roadmap](#roadmap)
* [Change Logs](#change-logs)
* [Contribution Guidelines](#contribution-guidelines)
* [Reporting a Security Vulnerability](#security-help)


# <a name="about"></a>About

* PHP Controllers ~ 1.2 MB (on disk)
* Blade HTML Template Views ~ 572 KB
* Javascript/jQuery within Blade Templates ~ 131 KB
* CSS within Blade Templates ~ 131 KB
* SurvLoop-Generated PHP Eloquent Data Table Models ~ 172 KB
* SurvLoop-Generated PHP Laravel Database Migration & Seeders ~ 229 KB

SurvLoop is a Laravel-based engine for websites dominated by the collection and publication of open data. 
This is a database design and survey generation system, though it will increasingly be a flexible tool to solve many 
web-based problems.

It is currently in continued, heavy development, with much happening here in early 2019, almost ready to go live. 
I plan to provide more documentation in the coming weeks. Thank you for your interest and patience!

This software was originally developed to build the 
<a href="https://github.com/flexyourrights/openpolice" target="_blank">Open Police</a> system. 
It began as an internal tool to design our database, then prototype survey generation. Then it was adapted to the 
Laravel framework, and has continued to grow towards a content-management system for data-focused websites.

The upcoming Open Police web app is the best live <b>beta demo</b> of the engine's end results, 
and feedback on that project and the SurvLoop user experience can be  via the end of the submission process:<br />
<a href="https://openpolice.org/filing-your-police-complaint" target="_blank">https://openpolice.org/filing-your-police-complaint</a><br />
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
<a href="https://drugstory.me" target="_blank">Drug Story</a> (less active).

The installation used for SurvLoop.org is currently the best example of a bare-bones extenstion of SurvLoop:<br />
<a href="https://github.com/wikiworldorder/survlooporg" target="_blank">github.com/wikiworldorder/survlooporg</a>


# <a name="requirements"></a>Requirements

* php: >=7.2
* <a href="https://packagist.org/packages/laravel/laravel" target="_blank">laravel/laravel</a>: 5.8.*
* <a href="https://packagist.org/packages/wikiworldorder/survloop-libraries" target="_blank">wikiworldorder/survloop-libraries</a>: 0.1.*

# <a name="getting-started"></a>Getting Started


### Install Laravel Using Composer

<a href="https://survloop.org/how-to-install-survloop" target="_blank">Full install instructions</a> also describe how to set up a development environment using VirutalBox, Vargrant, and Laravel's Homestead.

```
$ composer create-project laravel/laravel survloop "5.8.*"
$ cd survloop

```

Edit the environment file to connect the default MYSQL database:
```
$ nano .env
```
```
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
```

You could do things like install Laravel's out-of-the-box user authentication tools, and push the vendor file copies where they need to be:
```
$ php artisan make:auth
$ echo "0" | php artisan vendor:publish --tag=laravel-notifications
```

### Install WikiWorldOrder/SurvLoop

From your Laravel installation's root directory, update `composer.json` to require and easily reference OpenPolice:
```
$ nano composer.json
```
```
...
"require": {
    ...
    "wikiworldorder/survloop": "^0.2.8",
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
}, ...
```

After saving the file, run the update to download OpenPolice, and any missing dependencies.
```
$ composer update
```

Add the package to your application service providers in `config/app.php`.
```
$ nano config/app.php
```
```
...
'providers' => [
    ...
    SurvLoop\SurvLoopServiceProvider::class,
    ...
],
...
'aliases' => [
    ...
    'SurvLoop' => 'WikiWorldOrder\SurvLoop\SurvLoopFacade',
    ...
], ...
```

Swap out the OpenPolice user model in `config/auth.php`.
```
$ nano config/auth.php
```
```
...
'model' => App\Models\User::class,
...
```

Update composer, publish the package migrations, etc...
```
$ echo "0" | php artisan vendor:publish --force
$ cd ~/homestead
$ vagrant up
$ vagrant ssh
$ cd code/survloop
$ php artisan migrate
$ composer dump-autoload
$ php artisan db:seed --class=SurvLoopSeeder
$ php artisan db:seed --class=ZipCodeSeeder
$ php artisan optimize:clear
```

For now, to apply database design changes to the same installation you are working in, depending on your server, you might also need something like this...
```
$ chown -R www-data:33 app/Models
$ chown -R www-data:33 database
```

You might need to re-run some things outside the virtual box too, e.g.
```
$ exit
$ cd ~/homestead/code/survloop
$ php artisan optimize:clear
$ composer dump-autoload
```

### Initialize SurvLoop Installation

Then browsing to the home page should prompt you to create the first admin user account:

http://survloop.local

If everything looks janky, then manually load the style sheets, etc:

http://survloop.local/css-reload

After logging in as an admin, this link rebuilds many supporting files:

http://survloop.local/dashboard/settings?refresh=2

# <a name="documentation"></a>Documentation

## About SurvLoop's Codebase and Database Design

Better documentation is juuust beginning to be created...

<a href="https://survloop.org/package-files-folders-classes" target="_blank">survloop.org/package-files-folders-classes</a>

Once installed, documentation of this system's database design can be found at http://localhost/dashboard/db/all. This system's 
survey design can be found at http://localhost/dashboard/surv-1/map?all=1&alt=1
or publicly visible links like those above.

<a href="https://survloop.org/db/SL" target="_blank">survloop.org/db/SL</a>


# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release (**1.0**). It's my first time building on Laravel, or GitHub. So sorry.

* [ ] Correct all issues needed for minimum viable product, and launch Open Police Complaints.
* [ ] Integrate options for MFA using Laravel-compatible package.
* [ ] Upgrade database and graphic design for admin tools, thus far only used by the author. This should include database preparations for multi-lingual support.
* [ ] Code commenting, learning and adopting more community norms.
* [ ] Add decent levels of unit testing. Hopefully improve the organization of objects/classes.
* [ ] Improve import/export work flow for copying/moving installations.
* [ ] Generate all admin tools by SurvLoop itself.
* [ ] Add multi-lingual support on the Node-level (surveys and web pages), for starters, then database design.
* [ ] Finish migrating all raw queries to use Laravel's process.
* [ ] Convert more SurvLoop (older) code to take advantage of more Laravel built-in icapabilities.

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

