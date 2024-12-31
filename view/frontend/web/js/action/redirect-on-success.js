define(
    [
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote'
    ],
    function (url, fullScreenLoader, quote) {
        'use strict';

        return {
            redirectUrl: window.checkoutConfig.payment.amwal_payments.defaultRedirectUrl,

            /**
             * Provide redirect to page with order ID as a parameter
             */
            execute: function () {
                const quoteId = quote.getQuoteId();

                if (!quoteId) {
                    console.error('Quote ID not found');
                    return;
                }

                // Construct the redirect URL with the order ID as a query parameter
                const redirectWithOrderId = this.redirectUrl + '?quoteId=' + quoteId;

                fullScreenLoader.startLoader();
                window.location.replace(url.build(redirectWithOrderId));
            }
        };
    }
);
