define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'mage/translate',
    'mage/cookies',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Customer/js/customer-data',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!',
],
function ($, Component, $t) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment/form',
            amwalButtonId: 'amwal-place-order-button',
            amwalButtonContainer: null,
            isInitialized: false,
        },

        initialize: function () {
            let self = this;
            self._super();
            const applePayObserver = new MutationObserver((mutations) => {
                if (navigator.userAgent.indexOf('Safari') !== -1 && navigator.userAgent.indexOf('Chrome') === -1) {
                    let applePayLogo = document.getElementById('apple-pay-logo');
                    let secureText = document.getElementById('secure-text');
                    if (applePayLogo) {
                        applePayLogo.classList.remove('apple-pay');
                        if (secureText && secureText.innerHTML.indexOf('Apple Pay') === -1) {
                            secureText.innerHTML = $t('Pay securely with MADA, credit cards or with Apple Pay');
                        }
                    }
                }
            });
            applePayObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
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

            self.amwalButtonContainer = document.getElementById(self.amwalButtonId);
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalButtonContainer);
            }
            self.isInitialized = true;
        },
    });
});
