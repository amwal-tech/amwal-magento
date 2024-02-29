#!/bin/bash

cd $MAGENTO_ROOT

# tmp debug
echo "Env data:"
cat app/etc/env.php
exit 1


echo "Setting config values for Amwal Payments plugin"

# Set required config values
php bin/magento config:set currency/options/allow SAR,USD --quiet
php bin/magento config:set currency/options/base SAR --quiet
php bin/magento config:set currency/options/default SAR --quiet
php bin/magento config:set admin/security/admin_account_sharing 1 --quiet
php bin/magento config:set admin/security/use_form_key 0 --quiet
php bin/magento config:set cms/wysiwyg/enabled disabled --quiet
php bin/magento config:set payment/amwal_payments/active 1 --quiet
php bin/magento config:set payment/amwal_payments/express_checkout_active 1 --quiet
php bin/magento config:set payment/amwal_payments/regular_checkout_active 1 --quiet
php bin/magento config:set payment/amwal_payments/country_code SA --quiet
php bin/magento config:set payment/amwal_payments/dark_mode 0 --quiet
php bin/magento config:set payment/amwal_payments/order_confirmed_status "processing" --quiet
php bin/magento config:set payment/amwal_payments/allowspecific 0 --quiet
php bin/magento config:set payment/amwal_payments/debug_mode 0 --quiet
php bin/magento config:set payment/amwal_payments/express_checkout_title "Buy now with Amwal" --quiet
php bin/magento config:set payment/amwal_payments/hide_proceed_to_checkout 1 --quiet
php bin/magento config:set payment/amwal_payments/merchant_id_valid 1 --quiet
php bin/magento config:set payment/amwal_payments/create_user_on_order 1 --quiet
php bin/magento config:set payment/amwal_payments/use_base_currency 0 --quiet
php bin/magento config:set payment/amwal_payments/use_system_country_settings 1 --quiet
php bin/magento config:set payment/amwal_payments/merchant_id "sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47" --quiet

echo "Flushing Magento cache"
php bin/magento cache:flush

# (Re-)Compile DI
echo "Running DI Compilation command"
php bin/magento setup:di:compile --quiet

echo "Finished Amwal plugin configuration"
