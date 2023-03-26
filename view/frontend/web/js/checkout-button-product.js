define([
    'jquery',
    'Amwal_Payments/js/checkout-button-base',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'Magento_Catalog/js/product/view/product-info-resolver',
    'underscore',
    'domReady!'
],
function ($, Component, idsResolver, productInfoResolver, _) {
    'use strict';

    return Component.extend({
        productId: null,
        buttonSelectorPrefix: 'amwal-checkout-',
        qtySelector: 'input#qty',
        addToCartSelector: '#product-addtocart-button',
        productFormSelector: '#product_addtocart_form',
        listingProductFormSelector: 'form[data-role="tocart-form"]',
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

            if (self.isListing) {
                self.triggerContext = 'product-listing-page';
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
         * Start the express checkout flow.
         */
        startExpressCheckout: function () {
            let self = this;

            self.isClicked(true);
            $('body').trigger('processStart');

            self.updateOrderedAmount();

            self.checkAmount();

            let $form = $(self.productFormSelector);
            if (self.isListing) {
                $form = self.$checkoutButton.closest('.product-item-actions').find(self.listingProductFormSelector);
            }

            if ($form.length) {
                self.triggerAddToCart($form);
            } else {
                self.$checkoutButton.trigger('startAmwalCheckout', {});
            }
        },

        /**
         * @param {jQuery} $form
         */
        triggerAddToCart: function ($form) {
            var self = this,
                productIds = idsResolver($form),
                productInfo = productInfoResolver($form),
                formData;

            formData = new FormData($form[0]);

            $.ajax({
                url: $form.prop('action'),
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                    var eventData, parameters;

                    self.$checkoutButton.trigger('startAmwalCheckout', {
                        'sku': $form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': $form,
                        'response': res
                    });
                },
                error: function (res) {
                    $(document).trigger('ajax:addToCart:error', {
                        'sku': $form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': $form,
                        'response': res
                    });
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
