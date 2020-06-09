# DEPRECATED [![No Maintenance Intended](http://unmaintained.tech/badge.svg)](http://unmaintained.tech/)

This repository is now deprecated and it will be available until 01.06.2021.

Octopush
========

Unit tests:
[![Build Status](https://jenkins-dev.olxlatam.com/job/octopush_test_unit/badge/icon)](https://jenkins-dev.olxlatam.com/job/octopush_test_unit/)

Acceptance tests:
[![Build Status](https://jenkins-dev.olxlatam.com/job/octopush_staging_test_acceptance/badge/icon)](https://jenkins-dev.olxlatam.com/job/octopush_staging_test_acceptance/)

Octopush is an application to manage deployment requests executed by Jenkins.

Installation
------------
You can check the docker-environment folder and follow instructions on how to run it on Docker or Manually follow these instrucions: 
Octopush is built on PHP and MySQL. Its PHP dependencies are managed with composer but there are some components that should be installed manually:  

* PHP 5.3
* php5-mysql
* MySQL
* HttpRequest PHP Library (pecl_http)
* phpunit


Quickstart
--------------

After intalling the base components mentioned above, you can follow the steps below to get your Octopush instance running:
* Clone repo
* Get the composer installer by executing: _curl -s https://getcomposer.org/installer | php_
* Install dependencies: _php composer.phar install_
* Create database using the script /sqls/schema.sql: _mysql < schema.sql_
* Adjust dabatase and RM Jenkins settings in _src/config_
* Run tests by executing _phpunit_
* Adjust Apache configuration based on the snippet below
* Adjust hosts file to add _octopush.com_ entry
* Create log file: 
    <pre>
    mkdir src/logs
    touch src/logs/octopush.log
    chmod 777 src/logs/octopush.log
    </pre>
* Browse the application at demo.octopush.com


Apache configuration

    <VirtualHost *:80>
        ServerName  octopush.com
        ServerAlias demo.octopush.com

        DocumentRoot "/var/www/octopush/"

        <Directory "/var/www/octopush">
            Options -MultiViews
        AllowOverride None
            RewriteEngine On
            #RewriteBase /path/to/app
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [L]
        </Directory>

        ErrorLog            /var/log/octopush/error_log
        CustomLog           /var/log/octopush/access_log combined
    </VirtualHost>


Octopush API
------------

In the folder sample_scripts you can find some scripts that will show you how to interact with Octopush API.

More Doc
--------
http://olx-inc.github.io/octopush
####

