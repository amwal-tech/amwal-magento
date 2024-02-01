define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'payAmwalOrder',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'underscore',
    'mage/translate',
    'domReady!'
],
function ($, Component, placeAmwalOrder, payAmwalOrder, amwalErrorHandler, urlBuilder, customerData, _) {
    'use strict';

    return Component.extend({
        buttonId: null,
        triggerContext: null,
        productButtonContainer: null,
        amwalCheckoutButton: null,
        hideProceedToCheckout: false,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            let self = this;

            self.productButtonContainer = document.getElementById(self.buttonId);
            if (window.renderReactElement) {
                window.renderReactElement(self.productButtonContainer);

                if (self.triggerContext === 'product-detail-page' || self.triggerContext === 'product-listing-page') {
                    self.initializeProductDetail('product_addtocart_form');
                }
                if (self.triggerContext === 'amwal-widget' || self.triggerContext === 'product-list-widget') {

                    self.initializeProductDetail('form-' + self.buttonId);
                }
                if (self.triggerContext === 'minicart') {
                    self.initializeMiniCart();
                }

                window.addEventListener('cartUpdateNeeded', function(e) {
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                });
            }

            return self;
        },

        /**
         * Initializes the Product Detail Page specific actions
         */
        initializeProductDetail: function (formID) {
            let self = this;
            const amwalButtonObserver = new MutationObserver((mutations) => {
                const amwalCheckoutButton = self.productButtonContainer.querySelector('amwal-checkout-button');
                if (amwalCheckoutButton) {
                    amwalCheckoutButton.setAttribute('disabled', true);
                    addFormListeners();
                    amwalButtonObserver.disconnect();
                }
                const cart = customerData.get('cart');
                cart.subscribe(function (updatedCartData) {
                    if (updatedCartData.summary_count > 0) {
                        self.productButtonContainer.classList.add('hidden');
                    }else {
                        self.productButtonContainer.classList.remove('hidden');
                    }
                    addFormListeners();
                }, this);
            })
            amwalButtonObserver.observe(self.productButtonContainer, {
                childList: true,
                subtree: true,
                attributes: false,
                characterData: false
            });

            const addToCartForm = $("#" + formID);

            /**
             * Check if the product form is valid
             * @return Boolean
             */
            const isProductFormValid = () => {
                addToCartForm.validation();
                const formIsValid = addToCartForm.validation('isValid');
                addToCartForm.validation('clearError');
                return formIsValid;
            }

            /**
             * Check if the cart is empty
             * @return Boolean
             */
            const isCartEmpty = () => {
                const cart = customerData.get('cart');
                return cart().summary_count == 0;
            }

            /**
             * Toggle the button disabled attribute based on form status
             */
            const updateButtonStatus = () => {
                const amwalButton = $("#" + self.buttonId +" amwal-checkout-button");
                if (isCartEmpty()) {
                    amwalButton.removeClass('hidden');
                }else {
                    amwalButton.addClass('hidden');
                }
                if (isProductFormValid()) {
                    amwalButton.removeAttr('disabled');
                } else {
                    amwalButton.attr('disabled', true);
                }
            }

            /**
             * Listen to form changes to update button status.
             */
            const addFormListeners = () => {
                addToCartForm.ready(function() {
                    updateButtonStatus();
                })

                addToCartForm.on('change', function() {
                    updateButtonStatus();
                });
            }
        },

        /**
         * Initialize mini cart specific actions
         */
        initializeMiniCart: function () {
            let self = this;

            const proceedToCheckoutObserver = new MutationObserver((mutations) => {
                const proceedToCheckoutButton = document.getElementById('top-cart-btn-checkout')
                if (proceedToCheckoutButton){
                    self.productButtonContainer.classList.remove('hidden')
                    proceedToCheckoutButton.after(self.productButtonContainer)
                    if (self.hideProceedToCheckout) {
                        proceedToCheckoutButton.style.display = 'none'
                    }
                    proceedToCheckoutObserver.disconnect()
                }
            })
            proceedToCheckoutObserver.observe(document.getElementById('minicart-content-wrapper'), {
                childList: true,
                subtree: true,
                attributes: false,
                characterData: false
            });
        }
    });
});
