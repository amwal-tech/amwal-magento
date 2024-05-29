#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Updating entrypoint.sh to include code coverage reporting..."
sed -i 's|-c phpunit.xml|-c phpunit.xml --coverage-clover=coverage.xml|' ../../../entrypoint.sh
tail -n 1 ../../../entrypoint.sh
tail -n 20 ../../../docker-files/phpunit.xml

