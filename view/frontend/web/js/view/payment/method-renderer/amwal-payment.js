define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!'
],
function ($, Component, quote, totals, placeAmwalOrder, payAmwalOrder, urlBuilder) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment/form',
            pluginVersion: 0,
            amount: 0,
            triggerContext: 'regular-checkout',
            code: 'amwal_payments',
            amwalButtonId: 'amwal-place-order-button',
            amwalButtonSelector: '#amwal-place-order-button',
            additionalData: {},
            checkoutButton: null,
            $checkoutButton: null,
            isInitialized: false,
            receivedSuccess: false,
            busyUpdatingOrder: false
        },

        initialize: function () {
            let self = this;
            self._super();

            const eventListenerInterval = setInterval(function () {
                if (self.isInitialized) {
                    clearInterval(eventListenerInterval);
                } else {
                    self.initializeAmwalButton();
                }
            }, 250);

            return self;
        },

        initializeAmwalButton: function () {
            let self = this;

            if (!$(self.amwalButtonSelector).length) {
                return;
            }

            self.checkoutButton = document.getElementById(self.amwalButtonId);
            self.$checkoutButton = $(self.amwalButtonSelector);

            let totalAmount;
            if (self.useBaseCurrency()) {
                totalAmount = totals.totals().base_grand_total
            } else {
                totalAmount = totals.totals().grand_total
            }

            self.amount = parseFloat(totalAmount);
            self.setAmount();

            if (self.getAllowedAddressStates().length) {
                self.$checkoutButton.attr('allowed-address-states', JSON.stringify(self.getAllowedAddressStates()));
            }

            if (self.getAllowedAddressCities().length) {
                self.$checkoutButton.attr('allowed-address-cities', JSON.stringify(self.getAllowedAddressCities()));
            }

            self.addAmwalEventListers()

            self.setAmount();
            self.observeAmount();

            self.isInitialized = true;
        },

        addAmwalEventListers: function () {
            let self = this;

            // Use the preCheckoutTrigger to initiate the express checkout
            self.checkoutButton.addEventListener('amwalPreCheckoutTrigger', function (e) {
                self.checkoutButton.dispatchEvent(
                    new CustomEvent ('amwalPreCheckoutTriggerAck', {
                        detail: {
                            order_position: 'checkout',
                            plugin_version: 'Magento ' + self.pluginVersion
                        }
                    })
                );
            });

            // Place the order once we receive the checkout success event
            self.checkoutButton.addEventListener('amwalPrePayTrigger', function (e) {
                placeAmwalOrder.execute(
                    e.detail.id,
                    quote.getQuoteId(),
                    self.getRefId(),
                    self.getRefIdData(),
                    self.triggerContext,
                    false,
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
            let redirectUrl = urlBuilder.build('checkout/onepage/success');
            self.checkoutButton.addEventListener('updateOrderOnPaymentsuccess', function (e) {
                self.busyUpdatingOrder = true;
                payAmwalOrder.execute(self.placedOrderId, e.detail.orderId, self.checkoutButton).then((response) => {
                    self.busyUpdatingOrder = false;
                    if (response === true) {
                        if (self.receivedSuccess){
                            window.location.href = redirectUrl;
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
                window.location.href = redirectUrl;
            });

            // Triggered when the modal is closed
            self.checkoutButton.addEventListener('amwalDismissed', function (e) {
                $('body').trigger('processStop');
            });
        },

        getData: function () {
            let self = this,
                parent = self._super();

            return $.extend(true, parent, {
                'additional_data': self.additionalData
            });
        },

        setAmount: function () {
            this.$checkoutButton.attr('amount', this.amount);
        },

        /**
         * Validates the amount and updates it if needed
         */
        checkAmount: function () {
            let self = this,
                setAmount = parseFloat(self.$checkoutButton.attr('amount')),
                actualAmount = parseFloat(self.amount);

            if (setAmount !== actualAmount) {
                self.setAmount()
            }
        },

        observeAmount: function () {
            let self = this,
                $checkoutButton = $(self.amwalButtonSelector),
                amountObserver = new MutationObserver(function(mutations) {
                    self.checkAmount($checkoutButton);
                });

            amountObserver.observe(self.checkoutButton, {
                attributes: true,
                attributeFilter: ['amount']
            });
        },

        getRefId: function () {
            return window.checkoutConfig.payment[this.getCode()]['refId'];
        },

        getRefIdData: function () {
            return window.checkoutConfig.payment[this.getCode()]['refIdData'];
        },

        getMerchantId: function () {
            return window.checkoutConfig.payment[this.getCode()]['merchantId'];
        },

        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()]['title'];
        },

        getCountryCode: function () {
            return window.checkoutConfig.payment[this.getCode()]['countryCode'];
        },

        getLocale: function () {
            return window.checkoutConfig.payment[this.getCode()]['locale'];
        },

        getDarkMode: function () {
            return window.checkoutConfig.payment[this.getCode()]['darkMode'] ? 'on' : 'off';
        },

        getTestEnvironment: function () {
            let merchantMode = window.checkoutConfig.payment[this.getCode()]['merchantMode'];
            return merchantMode === 'test' ? 'qa' : null;
        },

        getAllowedCountries: function () {
            return JSON.stringify(window.checkoutConfig.payment[this.getCode()]['allowedCountries']);
        },

        getAllowedAddressStates: function () {
            return window.checkoutConfig.payment[this.getCode()]['allowedAddressStates'];
        },

        getAllowedAddressCities: function () {
            return window.checkoutConfig.payment[this.getCode()]['allowedAddressCities'];
        },

        useBaseCurrency: function () {
            return window.checkoutConfig.payment[this.getCode()]['useBaseCurrency'];
        },

        getPluginVersion: function () {
            return window.checkoutConfig.payment[this.getCode()]['pluginVersion'];
        }
    });
});
