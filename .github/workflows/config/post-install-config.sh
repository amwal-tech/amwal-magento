#!/bin/bash

echo "Setting config values"

# Static config in config.xml file
sed -i 's/<merchant_mode>live<\/merchant_mode>/<merchant_mode>test<\/merchant_mode>/g' vendor/amwal/payments/etc/config.xml
sed -i 's/<integration_test_run>0<\/integration_test_run>/<integration_test_run>1<\/integration_test_run>/g' vendor/amwal/payments/etc/config.xml
sed -i 's/<merchant_id><\/merchant_id>/<merchant_id>sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47<\/merchant_id>/g' vendor/amwal/payments/etc/config.xml

# Config available through Admin UI
bin/magento config:set currency/options/allow SAR,USD -q -c
bin/magento config:set currency/options/base SAR -q -c
bin/magento config:set currency/options/default SAR -q -c
bin/magento config:set currency/options/allow SAR -q -c
bin/magento config:set admin/security/admin_account_sharing 1 -q -c
bin/magento config:set admin/security/use_form_key 0 -q -c
bin/magento config:set cms/wysiwyg/enabled disabled -q -c
bin/magento config:set catalog/search/engine elasticsearch7 -q -c
bin/magento config:set catalog/search/elasticsearch7_server_hostname es -q -c
bin/magento config:set catalog/search/elasticsearch7_server_port 9200 -q -c
bin/magento config:set catalog/search/elasticsearch7_server_timeout 60 -q -c
bin/magento config:set payment/amwal_payments/active 1 -q -c
bin/magento config:set payment/amwal_payments/dark_mode 0 -q -c
bin/magento config:set payment/amwal_payments/order_confirmed_status "processing" -q -c
bin/magento config:set payment/amwal_payments/allowspecific 0 -q -c
bin/magento config:set payment/amwal_payments/debug_mode 0 -q -c
bin/magento config:set payment/amwal_payments/merchant_id_valid 1 -q -c
bin/magento config:set payment/amwal_payments/create_user_on_order 1 -q -c
bin/magento config:set payment/amwal_payments/use_base_currency 0 -q -c
bin/magento config:set payment/amwal_payments/use_system_country_settings 1 -q -c
bin/magento config:set payment/amwal_payments/merchant_id "sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47" -q -c
echo "Config values set"

echo "Flushing cache"
bin/magento cache:flush -q -c
echo "Cache flushed"
