#!/bin/bash

cd $MAGENTO_ROOT

echo "Enabling all modules"
php bin/magento module:enable --all --quiet
echo "Enabled all modules"

echo "Running DI compilation"
php bin/magento setup:di:compile --quiet
echo "Finished DI compilation"

echo "Running Setup Upgrade"
php bin/magento setup:upgrade --quiet
echo "Finished Setup Upgrade"