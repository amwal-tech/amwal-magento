#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Running PHPUnit with coverage..."
tail -n 1 ../../../entrypoint.sh
sed -i 's/../../../vendor/bin/phpunit -c phpunit.xml/../../../vendor/bin/phpunit -c phpunit.xml --coverage-clover=coverage.xml/g' ../../../entrypoint.sh
tail -n 1 ../../../entrypoint.sh
