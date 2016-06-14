FROM     ubuntu:14.04
RUN      apt-get -y update
RUN      apt-get -y upgrade

# Install apache, PHP, and supplimentary programs. curl and lynx-cur are for debugging the container.
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 libapache2-mod-php5 php5-mysql php5-gd php-pear php-apc php5-curl curl lynx-cur php5-memcached wget

# Enable apache mods.
RUN a2enmod php5
RUN a2enmod rewrite

# Update the PHP.ini file, enable <? ?> tags and quieten logging.
#RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /etc/php5/apache2/php.ini
#RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /etc/php5/apache2/php.ini

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
#ENV SYMFONY_ENV=prod

EXPOSE 80
WORKDIR /var/www/site

# Update the default apache site with the config we created.
ADD config/apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN mkdir /var/log/application
RUN chown www-data /var/log/application
RUN ln -sf /dev/stdout /var/log/apache2/error.log
RUN ln -sf /dev/stdout /var/log/apache2/access.log

# Download PHPunit
RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-4.8.23.phar \
&& chmod 755 /usr/local/bin/phpunit
# Download Composer
RUN wget -O /usr/local/bin/composer https://getcomposer.org/composer.phar \
&& chmod 755 /usr/local/bin/composer

# By default, simply start apache.
CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]
