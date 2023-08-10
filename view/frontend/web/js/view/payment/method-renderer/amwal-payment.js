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

            if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                let applePayButton = document.getElementsByClassName('apple-pay');
                setTimeout(function () {
                    applePayButton[0].classList.remove('apple-pay');
                }, 350);
            }
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
