#!/bin/bash

cd $MAGENTO_ROOT

echo "Copy global configuration"

cp vendor/amwal/payments/.github/workflows/config/config-global.php.dist
