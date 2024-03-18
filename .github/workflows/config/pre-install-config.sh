#!/bin/bash

echo "Generate global config file"
cat >$MAGENTO_ROOT/dev/tests/integration/etc/config-global.php <<"EOF"
<?php
return [
    'customer/password/limit_password_reset_requests_method' => 0,
    'admin/security/admin_account_sharing' => 1,
    'admin/security/limit_password_reset_requests_method' => 0,
    'currency/options/allow' => 'SAR,USD',
    'currency/options/base' => 'SAR',
    'currency/options/default' => 'SAR',
    'admin/security/use_form_key' => 0,
    'cms/wysiwyg/enabled' => 'disabled',
    'payment/amwal_payments/active' => 1,
    'payment/amwal_payments/dark_mode' => 0,
    'payment/amwal_payments/order_confirmed_status' => 'processing',
    'payment/amwal_payments/allowspecific' => 0,
    'payment/amwal_payments/debug_mode' => 0,
    'payment/amwal_payments/merchant_id_valid' => 1,
    'payment/amwal_payments/create_user_on_order' => 1,
    'payment/amwal_payments/use_base_currency' => 0,
    'payment/amwal_payments/use_system_country_settings' => 1,
    'payment/amwal_payments/merchant_id' => 'sandbox-amwal-e09ee380-d8c7-4710-a6ab-c9b39c7ffd47',
];

EOF
echo "Global config file generated"
