define([
    'jquery',
    'Amwal_Payments/js/checkout-button-base',
    'underscore',
    'domReady!'
],
function ($, Component, _) {
    'use strict';

    return Component.extend({
        productId: null,
        buttonSelectorPrefix: 'amwal-checkout-',
        qtySelector: 'input#qty',
        addToCartSelector: '#product-addtocart-button',
        productFormSelector: '#product_addtocart_form',
        superAttributeInputSelector: 'input[name^="super_attribute"]',
        productPrice: 0,
        orderedQty: 1,
        isConfigurable: false,
        configurableOptions: {},
        selectedConfigurableOptions: {},
        configuredProductId: null,
        isListing: false,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();

            let self = this;

            if (!self.isListing) {
                self.observeFormChange();
            }

            $(self.qtySelector).on('change', function() {
                self.updateOrderedQty();
                self.updateOrderedAmount();
                self.checkAmount()
            });

            self.setClickable(self.isProductFormValid());

            return self;
        },

        /**
         * Updates the ordered qty.
         */
        updateOrderedQty: function () {
            this.orderedQty = $(this.qtySelector).val();
        },

        /**
         * Updates the ordered price.
         */
        updateOrderedAmount: function()  {
            this.orderedAmount = this.orderedQty * this.productPrice;
        },

        /**
         * Observer to enable of disable the checkout button
         */
        observeFormChange() {
            let self = this,
                $form = $(this.productFormSelector);

            $form.on('change', function() {
                if (self.isConfigurable === true) {
                    self.updateSelectedConfigurableOptions();
                }
                if (!self.isProductFormValid()) {
                    self.setClickable(false);
                } else {
                    self.setClickable(true);
                }
            });
        },

        /**
         * Check if the product form is valid.
         * @return {boolean}
         */
        isProductFormValid() {
            if (this.isListing || this.isMinicart) {
                return true;
            }

            let $form = $(this.productFormSelector);
            $form.validation();

            let formIsValid = $form.validation('isValid');
            $form.validation('clearError')

            return formIsValid;
        },

        /**
         * Update the selected configurable options for configurable products.
         */
        updateSelectedConfigurableOptions() {
            let self = this;
                self.selectedConfigurableOptions = {};

            $(self.superAttributeInputSelector).each(function(k, v){
                let attributeId    = $(v).attr('name').split('[').pop().split(']')[0],
                    selectedOption = $(v).val();

                if (!attributeId || !selectedOption) {
                    return;
                }

                self.selectedConfigurableOptions[attributeId] = selectedOption.toString();
            });

            let productJsonConfig = $('[data-role=swatch-options]').data('mage-SwatchRenderer').options.jsonConfig,
                productIdIndex = productJsonConfig.index,
                productPriceIndex = productJsonConfig.optionPrices;

            let isProductMatched = false;
            $.each(productIdIndex, function(productId, productAttributes) {
                isProductMatched = _.isEqual(productAttributes, self.selectedConfigurableOptions);
                if (isProductMatched) {
                    self.configuredProductId = productId;
                    self.productPrice = productPriceIndex[productId].finalPrice.amount;
                }
            });
        },

        /**
         * Return the items that will be ordered.
         * @return {Array}
         */
        getOrderData() {
            let orderItems = [];

            orderItems.push({
                'product_id': this.productId,
                'configured_product_id': this.configuredProductId,
                'selected_configurable_options': this.selectedConfigurableOptions,
                'product_price': this.productPrice,
                'qty': this.orderedQty
            });

            return {
                'order_items': orderItems
            };
        }
    });
});
