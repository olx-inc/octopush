---
layout: default
title: Quickstart for developers
---

Octopush is built on PHP and MySql but some other tools are used for its development: 

* Depedencies are managed with Composer
* The UI uses jQuery and Bootstrap
* Unit tests are based on PHPUnit
* Acceptance tests are based on RSpec & Watir (Ruby)
* Git is used for version control
* Travis is used for running continuous integration
* Jenkins is used for running deployment task and acceptance tests
* HttpRequest PHP Library (pecl_http)

So to start developing follow the steps below (the procedure to install each item will depend on your OS):

* Install LAMP stack
* Install PECL HttpRequest Library
* Install Git
* Install Ruby 1.8 + RubyGems + Bundler
* Clone the repository
* Get the composer installer by executing: curl -s https://getcomposer.org/installer | php
* Install dependencies: php composer.phar install
* Create database using the script /sqls/schema.sql: mysql < schema.sql
* Create log file and grant read/write permissions
* Create control file and grant read/write permissions 
* Create a configuration file called config.yml by copying config.sample.yml
* Adjust the configuration file with the corresponding values
* Run tests by executing phpunit
* Adjust Apache configuration based on the snippet below
* Adjust hosts file to add octopush.com entry
* Run bundle install under test/Acceptance
* Run acceptance test under test/Acceptance by executing the script run_all_test.sh


Apache configuration


````
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
  ErrorLog            /var/log/octopush/error_log
  CustomLog           /var/log/octopush/access_log combined
  </Directory>
</VirtualHost>
````