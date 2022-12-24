define([
    'jquery',
    'Amwal_Payments/js/checkout-button-base',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'underscore',
    'domReady!'
],
function ($, Component, customerData, urlBuidler, _) {
    'use strict';

    return Component.extend({
        buttonSelectorPrefix: 'amwal-checkout-',
        minicartContentSelector: 'minicart-content-wrapper',
        minicartInitComplete: false,
        minicartShowActionSelector: 'a.action.showcart',
        minicartQtySelector: '.details-qty.qty input.cart-item-qty',
        proceedToCheckoutSelector: '#top-cart-btn-checkout',
        quotePrice: 0,
        orderedAmount: 0,
        checkoutButton: null,
        $checkoutButton: null,
        quoteId: null,
        hideProceedToCheckout: false,
        customerId: null,
        triggerContext: 'minicart',

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();

            this.processProceedToCheckoutButtonConfig();
            this.updateOrderedAmount();
            this.setClickable(true);

            return this;
        },

        /**
         * Get the selector for the checkout button.
         */
        getButtonSelector: function () {
            return this.buttonSelectorPrefix + 'quote-' + this.quoteId;
        },

        /**
         * Hides the proceed to checkout button if the configuration is set to do so.
         */
        processProceedToCheckoutButtonConfig: function () {
            let self = this,
                $minicartButtonWrapper = self.$checkoutButton.parent(),
                minicartContentWrapper = document.getElementById(self.minicartContentSelector);

            let proceedToCheckoutObserver = new MutationObserver(function(mutations) {
                let $proceedToCheckoutButton = $(self.proceedToCheckoutSelector),
                    $qtySelectors = $(self.minicartQtySelector);

                if ($minicartButtonWrapper.length && $proceedToCheckoutButton.length) {
                    $minicartButtonWrapper.insertAfter($proceedToCheckoutButton);
                    $minicartButtonWrapper.removeClass('hidden');

                    if (self.hideProceedToCheckout) {
                        $(self.proceedToCheckoutSelector).hide();
                    }

                    self.updateOrderedAmount();
                    self.checkAmount();

                    proceedToCheckoutObserver.disconnect();
                }
            });

            proceedToCheckoutObserver.observe(minicartContentWrapper, {
                childList: true,
                subtree: true,
                attributes: false,
                characterData: false
            });
        },

        /**
         * Updates the ordered price.
         */
        updateOrderedAmount: function() {
            this.orderedAmount = customerData.get('cart')._latestValue.subtotalAmount;
        },

        /**
         * Validates the amount and updates it if needed
         */
        checkAmount: function () {
            let self = this,
                setAmount = parseFloat(self.$checkoutButton.attr('amount')),
                actualAmount = parseFloat(self.orderedAmount);
            if (setAmount !== actualAmount) {
                self.$checkoutButton.attr('amount', actualAmount);
            }
        },

        /**
         * No order items are needed as we will retrieve the data based on the Quote.
         * @return {Array}
         */
        getOrderData() {
            return {
                'order_items': []
            };
        }
    });
});
