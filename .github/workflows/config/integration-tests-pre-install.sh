#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Adding xdebug extension"
if ! php -m | grep -i xdebug > /dev/null; then
    pecl install xdebug
    docker-php-ext-enable xdebug
fi
# Add xdebug configuration to php.ini
echo "Configuring xdebug"
echo "
[xdebug]
zend_extension=xdebug.so
xdebug.mode=coverage
xdebug.start_with_request=yes
xdebug.discover_client_host=true
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.idekey=PHPSTORM
" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

echo "Updating entrypoint.sh and phpunit.xml to include code coverage reporting..."
sed -i 's|-c phpunit.xml|-c phpunit.xml --coverage-cobertura=cobertura.xml|' ../../../entrypoint.sh
cp local-source/__extdn_github-actions-m2/.dev-tools/tests/integration/phpunit.xml ../../../docker-files/phpunit.xml
