define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!',
    'uiComponent'
],
function ($, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment/form',
            triggerContext: null,
            amwalButtonId: 'amwal-place-order-button',
            amwalButtonContainer: null,
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

            self.amwalButtonContainer = document.getElementById(self.amwalButtonId);
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalButtonContainer);
            }
        },
    });
});