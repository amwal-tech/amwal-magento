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
            template: 'Amwal_Payments/payment/amwal-payment-bank-installments/form',
            amwalBankInstallmentsButtonId: 'amwal-bank-installments-place-order-button',
            amwalBankInstallmentsButtonContainer: null,
            isInitialized: false,
        },

        initialize: function () {
            let self = this;
            self._super();
            self.getTitle = function () {
                return $t('Bank Installments (Amwal)');
            }
            self.getLocale = function () {
                return document.documentElement.lang;
            }
            self.getScopeCode = function () {
                return window.checkoutConfig.storeCode;
            }
            self.getButtonId = function () {
                return "amwal-bank-installments-checkout-" + Math.random().toString(36).substring(8);
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

            self.amwalBankInstallmentsButtonContainer = document.getElementById(self.amwalBankInstallmentsButtonId);
            self.amwalBankInstallmentsButtonContainer.setAttribute('data-locale', self.getLocale());
            self.amwalBankInstallmentsButtonContainer.setAttribute('data-scope-code', self.getScopeCode());
            self.amwalBankInstallmentsButtonContainer.setAttribute('data-button-id', self.getButtonId());
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalBankInstallmentsButtonContainer);
            }
            self.isInitialized = true;
        },
    });
});
