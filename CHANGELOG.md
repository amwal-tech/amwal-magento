# [1.0.33] (2024-03-4)
### Features
- [#240](https://github.com/amwal-tech/amwal-magento/pull/240) - Add Github Actions for CI/CD
- [#241](https://github.com/amwal-tech/amwal-magento/pull/241) - Add Pay Order & OrderUpdate return exception and report to sentry
- [#242](https://github.com/amwal-tech/amwal-magento/pull/242) - Add php unit
- [#243](https://github.com/amwal-tech/amwal-magento/pull/243) - Add order-details by orderId end-point
- [#248](https://github.com/amwal-tech/amwal-magento/pull/248) - Add SA as default country code
- [#249](https://github.com/amwal-tech/amwal-magento/pull/249) - Add Auto-Deploy to Store Server
- [#251](https://github.com/amwal-tech/amwal-magento/pull/249) - Add codecov integration
- [#258](https://github.com/amwal-tech/amwal-magento/pull/258) - Add Quote override settings
- [#259](https://github.com/amwal-tech/amwal-magento/pull/259) - Add the ScopeCode and check the AMWAL_CURRENCY
- [#260](https://github.com/amwal-tech/amwal-magento/pull/260) - Add Get cart button config unit testing
- [#261](https://github.com/amwal-tech/amwal-magento/pull/261) - Add Unit test Amwal button config interface
- [#263](https://github.com/amwal-tech/amwal-magento/pull/263) - Add PlaceOrderTest
- [#264](https://github.com/amwal-tech/amwal-magento/pull/264) - Add GetQuoteTest
- [#265](https://github.com/amwal-tech/amwal-magento/pull/265) - Add discount ribbon
- [#270](https://github.com/amwal-tech/amwal-magento/pull/270) - Add isCartEmpty check and keep the button rendered
- [#271](https://github.com/amwal-tech/amwal-magento/pull/271) - Add OrderUpdateTest
- [#272](https://github.com/amwal-tech/amwal-magento/pull/272) - Add Use placeOrder instead of quote submit method
- [#274](https://github.com/amwal-tech/amwal-magento/pull/274) - Add enable pre checkout trigger settings
- [#277](https://github.com/amwal-tech/amwal-magento/pull/277) - Add PayOrderTest
- [#279](https://github.com/amwal-tech/amwal-magento/pull/279) - Add Checkout button upgrade to 0.0.53-alpha-5
- [#280](https://github.com/amwal-tech/amwal-magento/pull/280) - Add extra check for the phone_number
- [#283](https://github.com/amwal-tech/amwal-magento/pull/283) - Add tax and fees Cart endpoint 
- [#284](https://github.com/amwal-tech/amwal-magento/pull/284) - Add Remove the button enabled settings
- [#285](https://github.com/amwal-tech/amwal-magento/pull/285) - Add Amwal checkout button id refactor.
- [#288](https://github.com/amwal-tech/amwal-magento/pull/288) - Add return preCheckoutCartId in preCheckoutTask
- [#289](https://github.com/amwal-tech/amwal-magento/pull/289) - Add debug option to react button
- [#293](https://github.com/amwal-tech/amwal-magento/pull/293) - Add upgrade version 0.0.53-alpha-7

### Bug Fixes
- [#245](https://github.com/amwal-tech/amwal-magento/pull/245) - Fix the Undefined "HTTP_HOST" in the cli runtime
- [#250](https://github.com/amwal-tech/amwal-magento/pull/250) - Fix the deployment script
- [#268](https://github.com/amwal-tech/amwal-magento/pull/268) - Fix OrderUpdate email subject and totals check.
- [#269](https://github.com/amwal-tech/amwal-magento/pull/269) - Fix permissive shipping methods
- [#273](https://github.com/amwal-tech/amwal-magento/pull/273) - Fix the isCartEmpty check
- [#275](https://github.com/amwal-tech/amwal-magento/pull/275) - Fix Change getAllItems to getAllVisibleItems
- [#276](https://github.com/amwal-tech/amwal-magento/pull/276) - Fix Add check for the method availability
- [#281](https://github.com/amwal-tech/amwal-magento/pull/281) - Fix discount rebion amount calculation
- [#287](https://github.com/amwal-tech/amwal-magento/pull/287) - Fix minor discount and tax change.
- [#290](https://github.com/amwal-tech/amwal-magento/pull/290) - Fix Deduct taxes and extraFees from the GrandTotal
- [#291](https://github.com/amwal-tech/amwal-magento/pull/291) - Fix cart get amount
- [#294](https://github.com/amwal-tech/amwal-magento/pull/294) - Fix Amount and discount refactor

# [1.0.32] (2023-12-4)
### Bug Fixes
- [#237](https://github.com/amwal-tech/amwal-magento/pull/237) - Downgrade sentry/sdk to 3.0.0 as minimum version
- [#235](https://github.com/amwal-tech/amwal-magento/pull/235) - Fix amwalOrderId return null in SalesOrder view page

# [1.0.31] (2023-11-29)
### Features
- Add order notifier to every order status change
- Add cronjob settings and run status
- Add order dataValidation
- Cancel existing orders based on Amwal Order ID
- Add additional checks to ensure address for guests do not get assigned a customer ID
- Amwal order data refactor

### Enhancements
- Change addRegularCheckoutButtonConfig to public method
- Add orderUrl in PayOrder
- Show Amwal order status action
- Add payment successful to handle amwal dismissed
- Check Order already exists
- Show Time zone format.
- Remove the order and create it again
- dataValidation fix getAmount and send email to admin
- Chnage the order status to pending_payment
- isPayValid return true in PayOrder

### Bug Fixes
- Amwal 305 fix is pay valid check in pay order.
- chnage entity_id to increment_id
- Fix the return message
- mailContent convert to string
- Amwal 305 fix is pay valid check in pay order.
- Add $isGuest to createAddress

# [1.0.30] (2023-11-16)
### Bug Fixes
- Remove the cancled order status from the pending orders update job
- Add canceled status to the order update

# [1.0.29](https://github.com/amwal-tech/amwal-magento/compare/v1.0.28...v1.0.29) (2023-11-07)
### Features
* QuoteId Refactor
* Add Enable setting for sentry report
* Change order_id from IncrementId to EntityId
* Add product image and url to order content
* Order setState
* SalesOrderGridPlugin
* Refactor getCityCodes
* Exclude REACT JS files from minify process
* AMWAL-288 handle extra address fields
* Add qty error message translate

### Bug Fixes
* Add cover case for the cartId
* AMWAL-289-CartId Create new QuoteMaskedId if is empty.

# [1.0.28](https://github.com/amwal-tech/amwal-magento/compare/v1.0.27...v1.0.28) (2023-10-26)
### Bug Fixes
* Fix-Undefined-variable-originalException

# [1.0.27](https://github.com/amwal-tech/amwal-magento/compare/v1.0.26...v1.0.27) (2023-10-25)
### Features
* use amwal-checkout-button from cdn
* Set data-locale
* Handle qty errors
* Add PaymentBrand to order details

### Bug Fixes
* reactivate quote in place-order to avoid logout

# [1.0.26](https://github.com/amwal-tech/amwal-magento/compare/v1.0.25...v1.0.26) (2023-10-21)
### Features
* Add Torod\CityRegion to CityHelper

### Bug Fixes
* fix pending_orders_update job

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
