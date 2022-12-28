define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'underscore',
    'mage/translate',
    'domReady!'
],
function ($, Component, placeAmwalOrder, urlBuilder, customerData, _) {
    'use strict';

    return Component.extend({
        productId: null,
        buttonSelectorPrefix: 'amwal-checkout-',
        isClickable: false,
        isClicked: false,
        orderedAmount: 0,
        addressData: {},
        refId: null,
        refIdData: {},
        checkoutButton: null,
        $checkoutButton: null,
        quoteId: null,
        triggerContext: 'product-detail-page',

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            let self = this,
                buttonSelector = self.getButtonSelector();

            self.checkoutButton = document.getElementById(buttonSelector);
            self.$checkoutButton = $(self.checkoutButton);

            // Handle express checkout trigger.
            self.$checkoutButton.on('click', function() {
                if (self.isClickable === true) {
                    self.startExpressCheckout();
                }
            });

            // Only add event listeners once the button is clicked to prevent each individual button from listening to events.
            self.isClicked.subscribe(function() {
                if (self.isClicked() === true) {
                    self.addAmwalEventListers(self.checkoutButton)
                }
            });

            return self;
        },

        /**
         * Get the selector for the checkout button.
         *
         * @return {String}
         */
        getButtonSelector: function () {
            return this.buttonSelectorPrefix + this.productId;
        },

        /**
         * Initialize observable properties.
         */
        initObservable: function () {
            this._super().observe(['isClicked']);
            return this;
        },

        /**
         * Adds the event listeners for the current checkout button
         */
        addAmwalEventListers: function () {
            let self = this;

            // Create the quote when address is updated so we can gather shipping info
            self.checkoutButton.addEventListener('amwalAddressUpdate', function (e) {
                self.addressData = e.detail;
                self.getQuote();
            });

            // Place the order once we receive the checkout success event
            self.checkoutButton.addEventListener('updateOrderOnPaymentsuccess', function (e) {
                placeAmwalOrder.execute(
                    e.detail.orderId,
                    self.quoteId,
                    self.refId,
                    self.refIdData,
                    self.triggerContext
                );
            });

            // Trigger the address update so Amwal knows the shippign methods are set
            window.addEventListener('amwalRatesSet', function () {
                window.dispatchEvent(new Event('amwalAddressAck'));
            });

            // Triggered when the modal is closed
            self.checkoutButton.addEventListener('amwalDismissed', function (e) {
                $('body').trigger('processStop');
            });
        },

        /**
         * Start the express checkout flow.
         */
        startExpressCheckout: function () {
            let self = this;

            self.isClicked(true);
            $('body').trigger('processStart');

            self.updateOrderedAmount();
            self.checkAmount();
        },

        /**
         * Updates the ordered price.
         *
         * @abstract
         */
        updateOrderedAmount: function() {
            throw "Abstract method updateOrderedAmount should be implemented in a child Component";
        },

        /**
         * Validates the amount and updates it if needed
         */
        checkAmount: function () {
            let self = this,
                setAmount = parseFloat(self.$checkoutButton.attr('amount')),
                actualAmount = parseFloat(self.orderedAmount);
            if (setAmount !== actualAmount) {
                self.$checkoutButton.attr('amount', actualAmount);
            }
        },

        /**
         * Set wether or not the button is clickable.
         * @param {bool} isClickable
         */
        setClickable(isClickable) {
            let self = this;
            if (!isClickable) {
                self.isClickable = false;
                self.$checkoutButton.attr('disabled', 'disabled');
            }
            if (isClickable) {
                self.isClickable = true;
                self.$checkoutButton.attr('disabled', false);
            }
        },

        /**
         * Retrieve the quote data from Magento based on the available order data.
         */
        getQuote() {
            let self = this,
                getQuoteEndpoint = urlBuilder.build('rest/V1/amwal/get-quote'),
                payload = self.getOrderData();

            payload.address_data = self.addressData;
            payload.ref_id = self.refId;
            payload.ref_id_data = self.refIdData;

            if (self.quoteId !== null && self.quoteId !== 'newquote') {
                payload.quote_id = self.quoteId;
            }

            $.ajax({
                url: getQuoteEndpoint,
                type: 'POST',
                data: JSON.stringify(payload),
                global: true,
                contentType: 'application/json',
                success: function (response) {
                    self.quoteId = response[0].quote_id;
                    self.orderedAmount = response[0].amount;

                    let amwalRates = [];

                    $.each(response[0].available_rates, function(rateCode, rateData) {
                        amwalRates.push({
                            'id': rateCode,
                            'label': rateData.carrier_title,
                            'price': rateData.price
                        });
                    });

                    self.$checkoutButton.attr('shipping-methods', JSON.stringify(amwalRates));
                    self.checkoutButton.shippingMethods = amwalRates;

                    self.$checkoutButton.attr('taxes', response[0].tax_amount);
                    self.checkoutButton.taxes = response[0].tax_amount;

                    self.$checkoutButton.attr('discount', response[0].discount_amount);
                    self.checkoutButton.discount = response[0].discount_amount;

                    window.dispatchEvent( new Event('amwalRatesSet') );
                },
                error: function (response) {
                    let message = self.getDefaultErrorMessage();
                    if (typeof response.responseJSON !== 'undefined' && typeof response.responseJSON.message !== 'undefined') {
                        message = response.responseJSON.message;
                    }
                    customerData.set('messages', {
                        'messages': [{
                            'type': 'error',
                            'text': message
                        }]
                    });
                }
            });
        },

        /**
         * Retrieve the data for what we want to order.
         * @abstract
         * @return {Array} Array containing order items
         */
        getOrderData() {
            throw "Abstract method updateOrderedAmount should be implemented in a child Component";
        },

        /**
         * Return the translated default error message.
         * @return {String}
         */
        getDefaultErrorMessage: function() {
            return $.mage.__('Something went wrong while placing your order.');
        }
    });
});
