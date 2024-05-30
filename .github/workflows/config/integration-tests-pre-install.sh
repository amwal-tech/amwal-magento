#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Adding xdebug extension"
docker-php-ext-install xdebug
echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini

echo "Updating entrypoint.sh and phpunit.xml to include code coverage reporting..."
sed -i 's|-c phpunit.xml|-c phpunit.xml --coverage-cobertura=cobertura.xml|' ../../../entrypoint.sh
cp local-source/__extdn_github-actions-m2/.dev-tools/tests/integration/phpunit.xml ../../../docker-files/phpunit.xml
