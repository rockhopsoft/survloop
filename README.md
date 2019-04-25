
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
* Blade HTML Template Views ~ 572 KB total
* Javascript/jQuery within Blade Templates ~ 131 KB
* CSS within Blade Templates ~ 131 KB
* SurvLoop-Generated PHP Eloquent Models ~ 172 KB
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


# <a name="requirements"></a>Requirements

* php: >=7.2
* <a href="https://packagist.org/packages/laravel/laravel" target="_blank">laravel/laravel</a>: 5.8.*
* <a href="https://packagist.org/packages/wikiworldorder/survloop-libraries" target="_blank">wikiworldorder/survloop-libraries</a>: 0.1.*

# <a name="getting-started"></a>Getting Started

## Installing SurvLoop with Laradock

First, <a href="https://www.docker.com/get-started" target="_blank">install Docker</a> on Mac, Windows, or an online server. 
Then grab a copy of Laravel (last tested with v5.8.3)...
```
$ git clone https://github.com/laravel/laravel.git opc
$ cd opc
```

Next, install and boot up Laradock (last tested with v7.14).
```
$ git submodule add https://github.com/Laradock/laradock.git
$ cd laradock
$ cp env-example .env
$ docker-compose up -d nginx mysql phpmyadmin redis workspace
```

After Docker finishes booting up your containers, enter the mysql container with the root password, "root". This seems to fix things for the latest version of MYSQL.
```
$ docker-compose exec mysql bash
# mysql --user=root --password=root default
mysql> ALTER USER 'default'@'%' IDENTIFIED WITH mysql_native_password BY 'secret';
mysql> exit;
$ exit
```

At this point, you should be able to browse to <a href="http://localhost:8080" target="_blank">http://localhost:8080</a> for PhpMyAdmin.
```
Server: mysql
Username: default
Password: secret
```

Finally, enter Laradock's workspace container to download and run the SurvLoop installation script.
```
$ docker-compose exec workspace bash
# git clone https://github.com/wikiworldorder/docker-survloop.git
# chmod +x ./docker-survloop/bin/*.sh
# ./docker-survloop/bin/survloop-laradock-postinstall.sh
```
And if all has gone well, you'll be asked to create a master admin user account when you browse to <a href="http://localhost/" target="_blank">http://localhost/</a>. If it loads, but looks janky (without CSS), reload the page once... and hopefully it looks like a fresh install.


## Installing SurvLoop without Laradock

The instructions below include the needed steps to install Laravel and SurvLoop.
For more on creating environments to host Laravel, you can find more instructions 
<a href="https://survloop.org/how-to-install-laravel-on-a-digital-ocean-server" target="_blank">on SurvLoop.org</a>.

### Use SurvLoop Install Script

If you've got PHP running, and Composer installed, you can just run this install script...

```
$ git clone https://github.com/wikiworldorder/docker-survloop.git
$ chmod +x ./docker-survloop/bin/*.sh
$ ./docker-survloop/bin/survloop-compose-install.sh ProjectFolderName
```

* Load in the browser to create super admin account and get started.

### Copy & Paste Install Commands

* Use Composer to install Laravel with default user authentication, one required package:

```
$ composer global require "laravel/installer"
$ composer create-project laravel/laravel SurvLoop "5.8.*"
$ cd SurvLoop
$ php artisan key:generate
$ php artisan make:auth
$ composer require wikiworldorder/survloop
$ sed -i 's/App\\User::class/App\\Models\\User::class/g' config/auth.php
```

* Update composer, publish the package migrations, etc...

```
$ echo "0" | php artisan vendor:publish --force
$ php artisan migrate
$ composer dump-autoload
$ php artisan db:seed --class=SurvLoopSeeder
$ php artisan db:seed --class=ZipCodeSeeder
```

* For now, to apply database design changes to the same installation you are working in, depending on your server, 
you might also need something like this...

```
$ chown -R www-data:33 app/Models
$ chown -R www-data:33 database
```

* Load in the browser to create super admin account and get started.

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

