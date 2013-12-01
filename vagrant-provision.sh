#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive

set -e

apt-get update
apt-get -y upgrade

apt-get install -y php5 curl

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin
chmod +x /usr/local/bin/composer.phar
ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

cd /usr/local/bin
wget https://phar.phpunit.de/phpunit.phar
chmod +x phpunit.phar
ln -s phpunit.phar phpunit

cd /vagrant
composer install
