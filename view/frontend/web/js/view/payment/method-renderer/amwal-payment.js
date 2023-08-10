define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!',
],
function ($, Component) {
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
                    if (applePayLogo) {
                        console.log('Apple Pay is not supported on Safari');
                        applePayLogo.classList.remove('apple-pay');
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
