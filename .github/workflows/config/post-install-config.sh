#!/bin/bash

cd $MAGENTO_ROOT

echo "Setting config values"

# Static config in config.xml file
sed -i 's/<merchant_mode>live<\/merchant_mode>/<merchant_mode>test<\/merchant_mode>/g' vendor/amwal/payments/etc/config.xml

# Config available through Admin UI
php bin/magento config:set currency/options/allow SAR,USD --quiet
php bin/magento config:set currency/options/base SAR --quiet
php bin/magento config:set currency/options/default SAR --quiet
php bin/magento config:set admin/security/admin_account_sharing 1 --quiet
php bin/magento config:set admin/security/use_form_key 0 --quiet
php bin/magento config:set cms/wysiwyg/enabled disabled --quiet
php bin/magento config:set catalog/search/engine elasticsearch7 --quiet
php bin/magento config:set catalog/search/elasticsearch7_server_hostname es --quiet
php bin/magento config:set catalog/search/elasticsearch7_server_port 9200 --quiet
php bin/magento config:set catalog/search/elasticsearch7_server_timeout 60 --quiet
php bin/magento config:set payment/amwal_payments/active 1 --quiet
php bin/magento config:set payment/amwal_payments/dark_mode 0 --quiet
php bin/magento config:set payment/amwal_payments/order_confirmed_status "processing" --quiet
php bin/magento config:set payment/amwal_payments/allowspecific 0 --quiet
php bin/magento config:set payment/amwal_payments/debug_mode 0 --quiet
php bin/magento config:set payment/amwal_payments/merchant_id_valid 1 --quiet
php bin/magento config:set payment/amwal_payments/create_user_on_order 1 --quiet
php bin/magento config:set payment/amwal_payments/use_base_currency 0 --quiet
php bin/magento config:set payment/amwal_payments/use_system_country_settings 1 --quiet
php bin/magento config:set payment/amwal_payments/merchant_id "sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47" --quiet
echo "Merchant ID:" php bin/magento config:show payment/amwal_payments/merchant_id --quiet
echo "Config values set"

echo "Flushing cache"
php bin/magento cache:flush --quiet
echo "Cache flushed"
