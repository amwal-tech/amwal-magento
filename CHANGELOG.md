# [1.0.25](https://github.com/amwal-tech/amwal-magento/compare/v1.0.24...v1.0.25) (2023-10-10)
### Features
* AMWAL-275 handle place order errors gracefully
* Show the get-quote exception message
* Use ES5 as target for amwal-magento-react-button
* Add Rest Api cors plugin
* add performSuccessRedirection method
* call dismissModal before redirection

### Bug Fixes
* Disallowed attribute amwal-checkout-button
* Customer getTelephone()
* Fix undefined variable in address data

# [1.0.24](https://github.com/amwal-tech/amwal-magento/compare/v1.0.23...v1.0.24) (2023-10-03)

### Features
* Change the button lang based on the current language
* Checkout page Amwal payment text translated
* Cron job for Update the Pending orders status
* Send the order products to Amwal transaction
* Show the Order Failure Reason in the order details page
* Show the order Amwal Payment Method in the order details page
* Add Use System Value for Country Specific Settings to the Amwal payment method settings
* [Mobile Display] Add End of the page checkout buttons to the mobile view
* Add CSS style for the Amwal payment setting
* Added store code to base URL for REST calls
* Add custom cities plugin

### Bug Fixes
* Fix the Initial data in regular checkout
* Always set Amwal client email address on quote + address
* Bypass user context for quote access check when using Amwal through headless Rest call

# [1.0.23](https://github.com/amwal-tech/amwal-magento/compare/v1.0.22...v1.0.23) (2023-09-14)

### Features
* Exclude REACT dist folder form JS merge
* Process refund through payment gateway command pool
* Add Default settings
* Add Order success info to onSuccessTask in react button
* Update amwal-checkout-button 0.0.52-alpha-11
* Update amwal-checkout-button 0.0.52-alpha-8
* Set Amwal Payment method in Place Order endpoint
* Add Installment URL comment
* Add onSuccessTask event
* Get shippingAddress from the quote
* Add Amwal Order Details
* Produce common js output for amwal-magento-react-button
* Update to 0.0.52-alpha
* Add overrideQuoteId
* Refactor handleAmwalAddressUpdate
* Include credentials in react fetches
* Add base URL to amwal magento react button
* Installment URL
* Add documentation and upgrade amwal-magento-react-button
* Phone number and getInitialAddressData refactor
* Amwal Products widget
* Add amwalAddressTriggerError
* Amwal 241 update customer information handling in magento
* Amwal 219 checkout page payment brands logos
* AMWAL-220 fill initial fields for final checkout
* Amwal 236 Amwal Modal Stuck at 'Payment Processed. Please wait to be redirected' After Successful Payment via Mini Cart
* Feature/button config endpoint
* Add react frontend implementation
* AMWAL-194 - Ensure fees are always updated to prevent old fees from showing up
* Amwal-194 - Added support for Amasty Extrafee module
* AMWAL-194 - Minor cleanup
* AMWAL-194 - Add ability to add additional fees
* AMWAL-168 - Only send order emails on successful payment

### Bug Fixes
* Fix refund calculation
* Fix for AMWAL-259 Firefox popup issue
* Fix compatability issue for PHP 7.x
* Update cart session
* Fix guest get initial address data
* Fix the phone number format
* Amwal 219 fix missing paramters
* AMWAL-157 - Fix missing constant errors
