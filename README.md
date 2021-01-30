
# RockHopSoft/Survloop

[![Laravel](https://img.shields.io/badge/Laravel-8.5-orange.svg?style=flat-square)](http://laravel.com)
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
Code bytes measured as stored on Mac disk:
* PHP Controllers ~ 1.9MB (44%)
* Blade HTML Template Views ~ 1.56MB (36%)
* Javascript/jQuery within Blade Templates ~ 201KB (5%)
* CSS within Blade Templates ~ 143KB (3%)
* Survloop-Generated PHP Eloquent Data Table Models ~ 221KB (5%)
* Survloop-Generated PHP Laravel Database Migration & Seeders ~ 291KB (7%)

Survloop is a Laravel-based engine for websites 
dominated by the collection and publication of open data. 
This is a database design and survey generation system, 
though it will increasingly be a flexible tool 
to solve many web-based problems.

It is currently in continued, heavy development, with 
much happening here in 2020, almost ready to go live. 
I plan to provide more documentation in the coming weeks. 
Thank you for your interest and patience!

This software was originally developed to build the 
<a href="https://github.com/flexyourrights/openpolice" 
    target="_blank">Open Police</a> 
system. It began as an internal tool to design our database, 
then prototype survey generation. Then it was adapted to the 
Laravel framework, and has continued to grow towards a 
content-management system for data-focused websites.

The upcoming Open Police web app is the best live install 
of the engine, and feedback on that project and the Survloop 
user experience can be via the end of the submission process:
<a href="https://openpolice.org/filing-your-police-complaint" target="_blank">https://openpolice.org/filing-your-police-complaint</a>
The resulting database designed using the engine, as well as 
the branching tree which specifies the user's experience: 
<a href="https://openpolice.org/db/OP" target="_blank">/db/OP</a>
<a href="https://openpolice.org/tree/complaint" target="_blank">/tree/complaint</a>
Among other methods, the resulting data can also be provided as 
XML included an automatically generated schema, eg.
<a href="https://openpolice.org/complaint-xml-schema" target="_blank">/complaint-xml-schema</a>
<a href="https://openpolice.org/complaint-xml-example" target="_blank">/complaint-xml-example</a>
<a href="https://openpolice.org/complaint-xml-all" target="_blank">/complaint-xml-all</a>

Other projects running Survloop:
<a href="https://powerscore.resourceinnovation.org/go-pro" target="_blank">Cannabis PowerScore</a> (<a href="https://github.com/resourceinnovation/cannabisscore" target="_blank">GitHub</a>).

The installation used for Survloop.org is currently the best example of a bare-bones extension of Survloop:<br />
<a href="https://github.com/rockhopsoft/survlooporg" target="_blank">github.com/rockhopsoft/survlooporg</a>


# <a name="requirements"></a>Requirements

* php: >=7.4
* <a href="https://packagist.org/packages/laravel/laravel" target="_blank">laravel/laravel</a>: 8.5.*
* <a href="https://packagist.org/packages/rockhopsoft/survloop-libraries" target="_blank">rockhopsoft/survloop-libraries</a>: 0.*

# <a name="getting-started"></a>Getting Started


### Install Laravel & Survloop on Homestead

<a href="https://survloop.org/how-to-install-survloop" target="_blank">Full install instructions</a> also describe how to set up a development environment using VirutalBox, Vargrant, and <a href="https://laravel.com/docs/8.x/homestead" target="_blank">Laravel's Homestead</a>. For these instructions, the new project directory is 'survproject'.

```
% composer create-project laravel/laravel survproject "8.5.*"
% cd survproject

```

Edit these lines of the environment file to connect the default MYSQL database:
```
% nano .env
```
```
APP_NAME="My Survloop Project"
APP_URL=http://survproject.local

DB_HOST=localhost
DB_PORT=33060
DB_CONNECTION=mysql
DB_DATABASE=survproject
DB_USERNAME=homestead
DB_PASSWORD=secret
```

Next, install Laravel's out-of-the-box user authentication tools, and Survloop:
```
% php artisan key:generate
% php artisan cache:clear
% COMPOSER_MEMORY_LIMIT=-1 composer require laravel/ui paragonie/random_compat mpdf/mpdf rockhopsoft/survloop
% php artisan ui vue --auth
% nano composer.json
```
From your Laravel installation's root directory, update `composer.json` to require and easily reference OpenPolice:
```
...
"autoload": {
    ...
    "psr-4": {
        ...
        "RockHopSoft\\Survloop\\": "vendor/rockhopsoft/survloop/src/",
    }
    ...
}, ...
```

It seems we also still need to manually edit `config/app.php`:
```
% nano config/app.php
```
```
...
'providers' => [
    ...
    RockHopSoft\Survloop\SurvloopServiceProvider::class,
    ...
],
...
'aliases' => [
    ...
    'Survloop' => 'RockHopSoft\Survloop\SurvloopFacade',
    ...
], ...
```

If installing on a server, you might also need to fix some permissions before the following steps...
```
% chown -R www-data:33 storage database app/Models
```

Clear caches and publish the package migrations...
```
% php artisan config:cache
% php artisan route:cache
% php artisan view:cache
% echo "0" | php artisan vendor:publish --force
% composer dump-autoload
% curl http://survproject.local/css-reload
```

With certain databases (like some managed by DigitalOcean), you may need to tweak the Laravel migration:
```
% nano database/migrations/2014_10_12_100000_create_password_resets_table.php
% sudo nano database/migrations/2019_08_19_000000_create_failed_jobs_table.php
```
Add this line before the "Schema::create" line in each file:
```
\Illuminate\Support\Facades\DB::statement('SET SESSION sql_require_primary_key=0');
```

Then initialize the database:
```
$ php artisan migrate
$ php artisan db:seed --class=SurvloopSeeder
$ php artisan db:seed --class=ZipCodeSeeder
$ php artisan db:seed --class=ZipCodeSeeder2
$ php artisan db:seed --class=ZipCodeSeeder3
$ php artisan db:seed --class=ZipCodeSeeder4
```

### Initialize Survloop Installation

Then browsing to the home page should prompt you to create the first admin user account:<br />
http://survloop.local

If everything looks janky, then manually load the style sheets, etc:<br />
http://survloop.local/css-reload

After logging in as an admin, this link rebuilds many supporting files:<br />
http://survloop.local/dashboard/settings?refresh=2

### Other Package Installation

The Excel tools use maatwebsite/excel, and you might need this on Ubuntu:
```
$ sudo apt-get install php7.4-zip
```
...or this on Mac:
```
$ brew update
$ brew install php@7.4
$ brew link php@7.4 --force
```


If you plan to generate PDFs, then you should also <a href="https://github.com/ArtifexSoftware/ghostpdl-downloads/releases/download/gs952/ghostpdl-9.52.tar.gz" target="_blank">download</a> and <a href="https://stackoverflow.com/questions/20798792/installing-ghostscript-with-vagrant#21417795" target="_blank">install</a> <a href="https://ghostscript.com/doc/current/Install.htm" target="_blank">Ghostscript</a>. This is for Ubuntu, it might already be installed:
```
$ sudo apt-get install ghostscript
```

This works in Homestead on Mac, with Homebrew:
```
$ brew install ghostscript
```

# <a name="documentation"></a>Documentation

## About Survloop's Codebase and Database Design

Better documentation is juuust beginning to be created...<br />
<a href="https://survloop.org/package-files-folders-classes" target="_blank">survloop.org/package-files-folders-classes</a>

Once installed, documentation of this system's database design can be found at http://localhost/dashboard/db/all. This system's survey design can be found at http://localhost/dashboard/surv-1/map?all=1&alt=1 or publicly visible links like those above.<br />
<a href="https://survloop.org/db/SL" target="_blank">survloop.org/db/SL</a>


# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release (**1.0**). It's my first time building on Laravel, or GitHub. So sorry.

* [ ] Correct all issues needed for minimum viable product, and launch Open Police Complaints.
* [ ] Integrate options for MFA using Laravel-compatible package.
* [ ] Upgrade database and graphic design for admin tools, thus far only used by the author.
* [ ] Code commenting, learning and adopting more community norms.
* [ ] Add decent levels of unit testing. Hopefully improve the organization of objects/classes.
* [ ] Improve import/export work flow for copying/moving installations.
* [ ] Generate all admin tools by Survloop itself.
* [ ] Add multi-lingual support on the Node-level (surveys and web pages), for starters, then database design.
* [ ] Multi-lingual support at every level.
* [ ] Convert more Survloop (older) code to take advantage of more Laravel built-in icapabilities.


# <a name="contribution-guidelines"></a>Contribution Guidelines

Please help educate me on best practices for sharing code in this community. Please report any issue you find in the issues page.

# <a name="security-help"></a>Reporting a Security Vulnerability

We want to ensure that Survloop is a secure HTTP open data platform for everyone. If you've discovered a security vulnerability in the Survloop software or Survloop.org, we appreciate your help in disclosing it to us in a responsible manner.

Publicly disclosing a vulnerability can put the entire community at risk. If you've discovered a security concern, please email us at rockhoppers *at* runbox.com. We'll work with you to make sure that we understand the scope of the issue, and that we fully address your concern. We consider correspondence sent to rockhoppers *at* runbox.com our highest priority, and work to address any issues that arise as quickly as possible.

After a security vulnerability has been corrected, a release will be deployed as soon as possible.

