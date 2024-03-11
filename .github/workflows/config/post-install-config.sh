#!/bin/bash

cd $MAGENTO_ROOT

echo "Running DI compilation"
php bin/magento setup:di:compile --quiet
echo "Finished DI compilation"