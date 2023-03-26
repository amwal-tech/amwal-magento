define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'payAmwalOrder',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'underscore',
    'mage/translate',
    'domReady!'
],
function ($, Component, placeAmwalOrder, payAmwalOrder, amwalErrorHandler, urlBuilder, customerData, _) {
    'use strict';

    return Component.extend({
        productId: null,
        buttonSelectorPrefix: 'amwal-checkout-',
        isClickable: false,
        isClicked: false,
        orderedAmount: 0,
        addressData: {},
        refId: null,
        pluginVersion: "",
        refIdData: {},
        checkoutButton: null,
        $checkoutButton: null,
        quoteId: null,
        placedOrderId: null,
        triggerContext: 'product-detail-page',
        redirectURL: undefined,
        receivedSuccess: false,
        busyUpdatingOrder: false,
        isPreCheckoutActive: false,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            let self = this,
                buttonSelector = self.getButtonSelector();

            self.checkoutButton = document.getElementById(buttonSelector);
            self.$checkoutButton = $(self.checkoutButton);

            self.redirectURL = undefined;
            self.receivedSuccess = false;
            self.busyUpdatingOrder = false;
            self.addAmwalEventListers();

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

            // Use the preCheckoutTrigger to initiate the express checkout
            self.checkoutButton.addEventListener('amwalPreCheckoutTrigger', function (e) {
                self.isPreCheckoutActive = true;
                self.$checkoutButton.on('amwalPreCheckoutComplete', function (event, data) {
                    self.checkoutButton.dispatchEvent(
                        new CustomEvent ('amwalPreCheckoutTriggerAck', {
                            detail: {
                                order_position: self.triggerContext,
                                plugin_version: 'Magento ' + self.pluginVersion,
                                order_content: JSON.stringify(self.getOrderData())
                            }
                        })
                    );
                });

                self.$checkoutButton.on('startAmwalCheckout', function (event, data) {
                    self.getQuote();
                });

                self.startExpressCheckout();
            });

            // Place the order once we receive the checkout success event
            self.checkoutButton.addEventListener('amwalPrePayTrigger', function (e) {
                placeAmwalOrder.execute(
                    e.detail.id,
                    self.quoteId,
                    self.refId,
                    self.refIdData,
                    self.triggerContext,
                    true,
                    self.checkoutButton
                ).then((response) => {
                    self.placedOrderId = response.entity_id;
                    let prePayTriggerPayload = {
                        detail: {
                            order_id: self.placedOrderId,
                            order_total_amount: response.total_due
                        }
                    };
                    self.checkoutButton.dispatchEvent(
                        new CustomEvent ('amwalPrePayTriggerAck', prePayTriggerPayload)
                    );
                });
            });

            // Pay the order after payment through Amwal is confirmed
            self.checkoutButton.addEventListener('updateOrderOnPaymentsuccess', function (e) {
                self.busyUpdatingOrder = true;
                payAmwalOrder.execute(self.placedOrderId, e.detail.orderId, self.checkoutButton).then((response) => {
                    self.busyUpdatingOrder = false;
                    if (response === true) {
                        self.redirectURL = urlBuilder.build('checkout/onepage/success');
                        if (self.receivedSuccess){
                            window.location.href = self.redirectURL;
                        }
                    }
                });
            });

            // Pay the order after payment through Amwal is confirmed
            self.checkoutButton.addEventListener('amwalCheckoutSuccess', function (e) {
                self.receivedSuccess = true; // coordinate with the updateOrderOnPaymentsuccess event
                if (self.busyUpdatingOrder) {
                    return; // the redirection will happen in the updateOrderOnPaymentsuccess event
                }
                if (self.redirectURL){
                    window.location.href = self.redirectURL;
                }
            });

            // Trigger the address update so Amwal knows the shippign methods are set
            window.addEventListener('amwalRatesSet', function () {
                self.checkoutButton.dispatchEvent(new Event('amwalAddressAck'));
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

            self.$checkoutButton.trigger('startAmwalCheckout', {});
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
            payload.trigger_context = self.triggerContext;
            payload.is_pre_checkout = self.isPreCheckoutActive;

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

                    self.$checkoutButton.attr('amount', response[0].subtotal);
                    self.checkoutButton.amount = response[0].subtotal;

                    self.$checkoutButton.attr('discount', response[0].discount_amount);
                    self.checkoutButton.discount = response[0].discount_amount;

                    if (self.isPreCheckoutActive) {
                        self.isPreCheckoutActive = false;
                        self.$checkoutButton.trigger('amwalPreCheckoutComplete');
                    } else {
                        window.dispatchEvent(new Event('amwalRatesSet'));
                    }
                },
                error: function (response) {
                    let message = null;
                    if (typeof response.responseJSON !== 'undefined' && typeof response.responseJSON.message !== 'undefined') {
                        message = response.responseJSON.message;
                    }

                    amwalErrorHandler.process(self.checkoutButton, message);
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
        }
    });
});
