define([
    'jquery',
    'Amwal_Payments/js/checkout-button-cart',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'underscore',
    'domReady!'
],
function ($, Component, customerData, urlBuidler, _) {
    'use strict';

    return Component.extend({
        minicartContentSelector: 'minicart-content-wrapper',
        minicartInitComplete: false,
        minicartShowActionSelector: 'a.action.showcart',
        minicartQtySelector: '.details-qty.qty input.cart-item-qty',
        proceedToCheckoutSelector: '#top-cart-btn-checkout',
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

            return this;
        },

        /**
         * Get the selector for the checkout button.
         */
        getButtonSelector: function () {
            return this.buttonSelectorPrefix + 'minicart-' + this.quoteId;
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
    });
});
