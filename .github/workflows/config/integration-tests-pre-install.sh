#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Running PHPUnit with coverage..."
cd local-source/__extdn_github-actions-m2
ls -la
