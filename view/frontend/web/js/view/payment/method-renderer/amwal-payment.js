define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Amwal_Payments/js/action/redirect-on-success',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'placeAmwalOrder',
    'payAmwalOrder',
    'mage/url',
    'domReady!',
],
function ($, Component, redirectOnSuccessAction, $t) {
    'use strict';
    window.addEventListener('cartUpdateNeeded', function(e) {
        const customerData = require('Magento_Customer/js/customer-data')
        customerData.invalidate(['cart']);
        customerData.reload(['cart'], true);
    });
    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/payment/amwal-payment/form',
            amwalButtonId: 'amwal-place-order-button',
            amwalButtonContainer: null,
            isInitialized: false,
            redirectAfterPlaceOrder: false,
        },

        initialize: function () {
            let self = this;
            self._super();
            self.getTitle = function () {
                return $t('Quick checkout (Amwal)');
            }
            self.getLocale = function () {
                return document.documentElement.lang;
            }
            self.getScopeCode = function () {
                return window.checkoutConfig.storeCode;
            }
            self.getButtonId = function () {
                return "amwal-checkout-" + Math.random().toString(36).substring(8);
            }
            const applePayObserver = new MutationObserver((mutations) => {
                applePayObserver.disconnect();
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        if (navigator.userAgent.indexOf('Safari') !== -1 && navigator.userAgent.indexOf('Chrome') === -1) {
                            handleApplePayLogo();
                        }else{
                            updateSecureTextContent($t('Pay securely with MADA and credit cards'));
                        }
                    }
                });
                // Reconnect the observer after handling changes
                applePayObserver.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
            // Initial observation setup
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

            function updateSecureTextContent(newContent) {
                const secureText = document.getElementById('secure-text');
                if (secureText) {
                    secureText.innerHTML = newContent;
                }
            }
            function handleApplePayLogo() {
                const applePayLogo = document.getElementById('apple-pay-logo');
                if (applePayLogo) {
                    applePayLogo.classList.remove('apple-pay');
                    updateSecureTextContent($t('Pay securely with MADA, credit cards or with Apple Pay'));
                }
            }

            return self;
        },
        validateForm: function() {
            const form = document.querySelector('#co-payment-form');
            if (!form) return false;
            const hasEmptyRequired = form.querySelectorAll('[required]').length > form.querySelectorAll('[required]:valid').length;
            const hasPaymentMethod = form.querySelector('input[name="payment[method]"]:checked');
            return !hasEmptyRequired && !!hasPaymentMethod;
        },

        toggleButtonState: function(isValid) {
            const amwalButton = document.querySelector('.amwal-checkout-button');
            if (amwalButton) {
                amwalButton.disabled = !isValid;
                amwalButton.style.opacity = isValid ? '1' : '0.5';
            }
        },

        initializeAmwalButton: function () {
            let self = this;

            self.amwalButtonContainer = document.getElementById(self.amwalButtonId);
            if (!self.amwalButtonContainer) {
                // Element not found in DOM yet, will try again later
                return;
            }
            // Validate form before initializing
            const isValid = self.validateForm();
            self.amwalButtonContainer.setAttribute('data-locale', self.getLocale());
            self.amwalButtonContainer.setAttribute('data-scope-code', self.getScopeCode());
            self.amwalButtonContainer.setAttribute('data-button-id', self.getButtonId());
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalButtonContainer);
            }
            // Set button state after render
            setTimeout(() => {
                self.toggleButtonState(isValid);
                const amwalButton = document.querySelector('.amwal-checkout-button');
                if (amwalButton) {
                    amwalButton.addEventListener('click', function(e) {
                        if (!self.validateForm()) {
                            e.preventDefault();
                            alert('Please fill in all required fields and select a payment method');
                            return false;
                        }
                    });
                }
            }, 100);
            if (window.checkoutConfig.payment.amwal_payments.isRegularCheckoutRedirect) {
                self.amwalButtonContainer.style.display = 'none';
                const amwalPlaceOrderButtons = document.querySelectorAll('.amwal-place-order');
                amwalPlaceOrderButtons.forEach(button => {
                    button.style.setProperty('display', 'block', 'important');
                });
            }
            self.isInitialized = true;
        },
        afterPlaceOrder: function (data, event) {
            redirectOnSuccessAction.execute();
        },
    });
});
