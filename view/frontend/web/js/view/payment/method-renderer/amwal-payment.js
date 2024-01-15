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
            self.getTitle = function () {
                return $t('Quick checkout (Amwal)');
            }
            self.getLocale = function () {
                return document.documentElement.lang;
            }
            self.getScopeCode = function () {
                return window.checkoutConfig.storeCode;
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

        initializeAmwalButton: function () {
            let self = this;

            self.amwalButtonContainer = document.getElementById(self.amwalButtonId);
            self.amwalButtonContainer.setAttribute('data-locale', self.getLocale());
            self.amwalButtonContainer.setAttribute('data-scope-code', self.getScopeCode());
            if (window.renderReactElement) {
                window.renderReactElement(self.amwalButtonContainer);
            }
            self.isInitialized = true;
        },
    });
});
