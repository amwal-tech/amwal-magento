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
        orderedAmount: 0,
        checkoutButton: null,
        $checkoutButton: null,
        quoteId: null,
        hideProceedToCheckout: false,
        customerId: null,
        triggerContext: 'cart',

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();

            this.updateOrderedAmount();
            this.setClickable(true);

            return this;
        },

        /**
         * Get the selector for the checkout button.
         */
        getButtonSelector: function () {
            return this.buttonSelectorPrefix + 'cart-' + this.quoteId;
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
