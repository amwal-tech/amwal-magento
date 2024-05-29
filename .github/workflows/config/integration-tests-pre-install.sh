#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Running PHPUnit with coverage..."
ls -lh
cd ~
ls -lh
echo $MAGENTO_ROOT

cd ~ && cd $MAGENTO_ROOT/dev/tests/integration && ../../../vendor/bin/phpunit -c phpunit.xml --coverage-clover=coverage.xml
