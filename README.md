# Amwal Payments Module for Magento 2

This plugin integrates the Amwal Payments Service in your Magneto 2 store.

## Requirements
- Magento 2.4.4 or higher
- PHP 7.4 or higher

## Getting the plugin

### Using composer (Recommended)

Go to your magento root directory in your server

From the command prompt or terminal run the following commands in given order:

1. Add the amwal payments repository
```shell
composer config repositories.amwal_payments git https://github.com/amwal-tech/amwal-magento.git
```

2. Require the composer package
```shell
composer require amwal/payments
```

### By Download

1. Download the zip from https://github.com/amwal-tech/amwal-magento/ by clicking Code > Download Zip
2. Go to your magento root directory in your server
3. Go to app/code directory
4. Create the directory "Amwal/Payments"
5.  Unzip the code in <your-magento-root>/app/code/Amwal/Payments


## Enabling the plugin

From the command prompt or terminal run the following commands to enable the plugin:

1. Enable the module in Magento
```shell
bin/magento module:enable Amwal_Payments
```

2. Run the Magneto Setup Upgrade command, Compile DI, Deploy static content, and finally flush the cache
```shell
bin/magento setup:upgrade && \
bin/magento setup:di:compile && \
bin/magento setup:static-content:deploy && \
bin/magento cache:flush
```

## Configuring the plugin
To enable and confige the payment method login to your store's admin panel and navigate to Stores > Configuration > Sales > Payment methods.

### Required configuration
There are some configuration values that are required for the plugin to function. These are the following:

| Configuration           | Description                                                                                                                                                                                     |
|-------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Merchant ID             | This can be requested by signing up as a merchant [here](https://merchant.sa.amwal.tech/)                                                                                                       |
| Reference ID secret key | This is used to generate a reference ID which ensures a payment is valid. You can fill in any random string here, but we suggest using [strong password generator](https://www.lastpass.com/features/password-generator) to ensure maximum security |

### Enabling the payment method
2. Under the Amwal Payments tab set the "Enabled" configuration to "Yes".
3. To enable the express checkout, set the "Enable Express Checkout" option to "Yes".
4. To enable the regular checkout, set the "Enable Regular Checkout" option to "Yes".
