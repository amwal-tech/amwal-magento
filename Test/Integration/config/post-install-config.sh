#!/bin/bash

cd $MAGENTO_ROOT

echo "Setting config values for Amwal Payments plugin"

# Set required config values
php bin/magento config:set -q currency/options/allow SAR,USD
php bin/magento config:set -q currency/options/base SAR
php bin/magento config:set -q currency/options/default SAR
php bin/magento config:set -q admin/security/admin_account_sharing 1
php bin/magento config:set -q admin/security/use_form_key 0
php bin/magento config:set -q cms/wysiwyg/enabled disabled
php bin/magento config:set -q payment/amwal_payments/active 1
php bin/magento config:set -q payment/amwal_payments/express_checkout_active 1
php bin/magento config:set -q payment/amwal_payments/regular_checkout_active 1
php bin/magento config:set -q payment/amwal_payments/country_code SA
php bin/magento config:set -q payment/amwal_payments/dark_mode 0
php bin/magento config:set -q payment/amwal_payments/order_confirmed_status processing
php bin/magento config:set -q payment/amwal_payments/allowspecific 0
php bin/magento config:set -q payment/amwal_payments/specificcountry ""
php bin/magento config:set -q payment/amwal_payments/debug_mode 0
php bin/magento config:set -q payment/amwal_payments/express_checkout_title Buy now with Amwal
php bin/magento config:set -q payment/amwal_payments/merchant_mode live
php bin/magento config:set -q payment/amwal_payments/hide_proceed_to_checkout 1
php bin/magento config:set -q payment/amwal_payments/merchant_id_valid 1
php bin/magento config:set -q payment/amwal_payments/create_user_on_order 1
php bin/magento config:set -q payment/amwal_payments/use_base_currency 0
php bin/magento config:set -q payment/amwal_payments/use_system_country_settings 1
php bin/magento config:set -q payment/amwal_payments/merchant_id sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47
php bin/magento config:sensitive:set -q payment/amwal_payments/ref_id_secret 1234567890
php bin/magento -q cache:flush

echo "Finished Amwal plugin configuration"
