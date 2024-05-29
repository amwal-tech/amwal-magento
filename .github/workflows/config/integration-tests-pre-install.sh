#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Updating entrypoint.sh to include code coverage reporting..."
sed -i 's|-c phpunit.xml|-c phpunit.xml --coverage-cobertura=cobertura.xml|' ../../../entrypoint.sh

echo "Updated phpunit.xml"
cp local-source/__extdn_github-actions-m2/.dev-tools/tests/integration/phpunit.xml ../../../docker-files/phpunit.xml
