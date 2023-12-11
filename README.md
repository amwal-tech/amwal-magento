<p align="center">
  <a href="https://amwal.tech/?utm_source=github&utm_medium=logo" target="_blank">
    <img src="https://uploads-ssl.webflow.com/62294ce746440b7bc08b4fc5/624352eb48193d537d329386_1-2-p-500.png" alt="Amwal" width="180" height="60">
  </a>
</p>

# Amwal Payments Module for Magento 2

[![Latest Stable Version](https://poser.pugx.org/amwal/payments/v/stable)](https://packagist.org/packages/amwal/payments)
[![License](https://poser.pugx.org/amwal/payments/license)](https://packagist.org/packages/amwal/payments)
[![Total Downloads](https://poser.pugx.org/amwal/payments/downloads)](https://packagist.org/packages/amwal/payments)
[![Monthly Downloads](https://poser.pugx.org/amwal/payments/d/monthly)](https://packagist.org/packages/amwal/payments)
[![PHP Version Require](http://poser.pugx.org/amwal/payments/require/php)](https://packagist.org/packages/amwal/payments)
[![npm](https://img.shields.io/npm/v/amwal-magento-react-button)](https://www.npmjs.com/package/amwal-magento-react-button)
[![CI status](https://github.com/amwal-tech/amwal-magento/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/amwal-tech/amwal-magento/actions)
[![Type coverage](https://shepherd.dev/github/amwal-tech/amwal-magento/coverage.svg)](https://shepherd.dev/github/amwal-tech/amwal-magento)

Amwal is an emerging leader in authentication, identity orchestration, and frictionless payment solutions.

## Table of Contents
  - [Getting started](#getting-started)
  - [Requirements](#requirements)
  - [Composer Installation](#composer-installation)
  - [Manual Installation](#manual-installation)
  - [Enabling the plugin](#enabling-the-plugin)
  - [Configuration](#configuration)
  - [More Information](#more-information)
  - [Support](#support)

## Getting started

### Requirements
- Magento 2.4.4 or higher
- PHP 7.4 or higher

### Composer Installation
To install the Module you will need to be using [Composer]([https://getcomposer.org/)
in your project. To install it please see the [docs](https://getcomposer.org/download/).

This plugin integrates the Amwal Payments Service in your Magneto 2 store.
```bash
composer require amwal/payments
```

### Manual Installation
1. Download the zip from  [GitHub repo]([https://github.com/amwal-tech/amwal-magento/]) by clicking Code > Download Zip
2. Go to your magento root directory in your server
3. Go to app/code directory
4. Create the directory "Amwal/Payments"
5.  Unzip the code in <your-magento-root>/app/code/Amwal/Payments


### Enabling the plugin

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

### Configuration
To configure the plugin login to your store's admin panel and navigate to Stores > Configuration > Sales > Payment methods. 
Under the Amwal Payments tab set the "Enabled" configuration to "Yes" and fill in the required configuration values.

| Configuration            | Description                                                                                                                                                                                     |
|--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Merchant ID              | This can be requested by signing up as a merchant [here](https://merchant.sa.amwal.tech/)                                                                                                       |
| Secret Key               | This can be requested by signing up as a merchant [here](https://merchant.sa.amwal.tech/)                                                                                                       |


## More Information
For more information about the Amwal and its features, visit [amwal.tech](https://amwal.tech).

## Support
For any issues or questions, please reach out to [support@amwal.tech](mailto:support@amwal.tech).
