define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!',
],
function ($, Component, $t) {
    'use strict';

    window.addEventListener('cartUpdateNeeded', function(e) {
        const customerData = require('Magento_Customer/js/customer-data')
        customerData.invalidate(['cart']);
        customerData.reload(['cart'], true);
    });

    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment-apple-pay/form',
            amwalApplePayButtonId: 'amwal-apple-pay-place-order-button',
            amwalApplePayButtonContainer: null,
            isInitialized: false,
        },

        initialize: function () {
            let self = this;
            self._super();
            self.getTitle = function () {
                return $t('Apple Pay (Amwal)');
            }
            self.getLocale = function () {
                return document.documentElement.lang;
            }
            self.getScopeCode = function () {
                return window.checkoutConfig.storeCode;
            }
            self.getButtonId = function () {
                return "amwal-apple-pay-checkout-" + Math.random().toString(36).substring(8);
            }
            const eventListenerInterval = setInterval(function () {
                if (self.isInitialized) {
                    clearInterval(eventListenerInterval);
                } else {
                    self.initializeAmwalButton();
                    updateSecureTextContent($t('Pay securely with Apple Pay, It\'s only available on Safari browser.'));
                }
            }, 250);

            function updateSecureTextContent(newContent) {
                const secureText = document.getElementById('apple-pay-secure-text');
                if (secureText) {
                    secureText.innerHTML = newContent;
                }
            }

            return self;
        },

        initializeAmwalButton: function () {
            let self = this;

            self.amwalApplePayButtonContainer = document.getElementById(self.amwalApplePayButtonId);
            self.amwalApplePayButtonContainer.setAttribute('data-locale', self.getLocale());
            self.amwalApplePayButtonContainer.setAttribute('data-scope-code', self.getScopeCode());
            self.amwalApplePayButtonContainer.setAttribute('data-button-id', self.getButtonId());
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalApplePayButtonContainer);
            }
            self.isInitialized = true;
        },
    });
});
