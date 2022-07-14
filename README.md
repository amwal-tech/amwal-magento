# Amwal Payments Module for Magento 2


## Installation

Using composer:

Go to your magento root directory in your server

From the command prompt or terminal run the following commands in given order:

$  composer config repositories.amwal_payments git https://github.com/amwal-tech/amwal-magento.git

$  composer require Amwal/Payments:dev-main

$  bin/magento module:enable Amwal_Payments --clear-static-content

$  bin/magento setup:upgrade

$  bin/magento setup:di:compile

$  bin/magento setup:static-content:deploy -f

$  bin/magento cache:flush


You should be good to go.


By Download:

Download the zip from https://github.com/amwal-tech/amwal-magento/ by clicking Code > Download Zip

Go to your magento root directory in your server

Go to app/code directory

Create the directories "Amwal/Payments" in <your-magento-root>/app/code

Unzip the code in <your-magento-root>/app/code/Amwal/Payments

Go back to the magento root directory

From the command prompt or terminal run the following commands in given order:


$  bin/magento module:enable Amwal_Payments --clear-static-content

$  bin/magento setup:upgrade

$  bin/magento setup:di:compile

$  bin/magento setup:static-content:deploy -f

$  bin/magento cache:flush


You should be good to go.
