define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'domReady!'
],
function ($, Component, quote, totals, placeAmwalOrder) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment/form',
            amount: 0,
            code: 'amwal_payments',
            amwalButtonId: 'amwal-place-order-button',
            amwalButtonSelector: '#amwal-place-order-button',
            additionalData: {}
        },

        initialize: function () {
            let self = this;

            self._super();

            self.amount = parseFloat(totals.totals().base_grand_total);
            self.setAmount();

            $(self.amwalButtonSelector).on('click', function (e) {
                $('body').trigger('processStart');
            });

            const eventListenerInterval = setInterval(function () {
                let $amwalCheckoutButton = $(self.amwalButtonSelector);

                if ($amwalCheckoutButton.length) {
                    document.getElementById(self.amwalButtonId).addEventListener('amwalCheckoutSuccess', function (e) {
                        self.additionalData.transactionId = e.detail.orderId;
                        placeAmwalOrder.execute(e.detail.orderId, quote.getQuoteId(), self.getRefId(), self.getRefIdData());
                    });
                    self.setAmount();
                    self.observeAmount();
                    clearInterval(eventListenerInterval);
                }

            }, 250);


            return self;
        },
        getData: function () {
            let self = this,
                parent = self._super();

            return $.extend(true, parent, {
                'additional_data': self.additionalData
            });
        },

        setAmount: function () {
            $(this.amwalButtonSelector).attr('amount', this.amount);
        },

        /**
         * Validates the amount and updates it if needed
         */
        checkAmount: function () {
            let self = this,
                $checkoutButton = $(self.amwalButtonSelector),
                setAmount = parseFloat($checkoutButton.attr('amount')),
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

            amountObserver.observe(document.getElementById(self.amwalButtonId), {
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
            return merchantMode === 'test' ? 'dev' : null;
        },

    });
});
