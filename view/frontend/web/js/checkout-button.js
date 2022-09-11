define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/confirm',
    'underscore',
    'mage/translate',
    'domReady!'
],
function ($, Component, placeAmwalOrder, urlBuilder, customerData, confirm, _) {
    'use strict';

    return Component.extend({
        productId: null,
        buttonSelectorPrefix: 'amwal-checkout-',
        qtySelector: 'input#qty',
        addToCartSelector: '#product-addtocart-button',
        productFormSelector: '#product_addtocart_form',
        superAttributeInputSelector: 'input[name^="super_attribute"]',
        isClickable: false,
        isClicked: false,
        productPrice: 0,
        orderedQty: 1,
        orderedAmount: 0,
        addressData: {},
        refId: null,
        checkoutButton: null,
        $checkoutButton: null,
        refIdData: {},
        isConfigurable: false,
        configurableOptions: {},
        selectedConfigurableOptions: {},
        configuredProductId: null,
        quoteId: null,
        isListing: false,
        isMinicart: false,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();

            let self = this,
                buttonSelector = self.buttonSelectorPrefix + self.productId,
                $minicartContentWrapper = $('#minicart-content-wrapper');

            if (self.isMinicart) {
                buttonSelector = self.buttonSelectorPrefix + 'quote-' + self.quoteId;
            }

            self.checkoutButton = document.getElementById(buttonSelector);
            self.$checkoutButton = $(self.checkoutButton);

            if (!self.isListing && !self.isMinicart) {
                self.observeFormChange();
            }

            self.observeAmount();

            if (self.isMinicart) {
                let $minicartButtonWrapper = self.$checkoutButton.parent();
                if ($minicartButtonWrapper.length) {
                    $minicartContentWrapper.append($minicartButtonWrapper);
                    $minicartButtonWrapper.removeClass('hidden');
                }
            }

            self.$checkoutButton.on('click', function() {
                if (self.isClickable === true) {
                    self.startExpressCheckout();
                }
            });

            $minicartContentWrapper.on('click', '#' + buttonSelector, function() {
                self.startExpressCheckout();
            });

            $(self.qtySelector).on('change', function() {
                self.updateOrderedQty();
                self.updateOrderedPrice();
                self.checkAmount()
            });

            // Only add event listeners once the button is clicked to prevent each individual button from listening
            self.isClicked.subscribe(function() {
                if (self.isClicked() === true) {
                    self.addAmwalEventListers(self.checkoutButton)
                }
            });

            self.setClickable(self.isProductFormValid());

            return self;
        },

        initObservable: function () {
            this._super().observe(['isClicked']);
            return this;
        },

        /**
         * Adds the event listeners for the current checkout button
         */
        addAmwalEventListers: function () {
            let self = this;

            // Create the quote when address is updated so we can gather shipping info
            self.checkoutButton.addEventListener('amwalAddressUpdate', function (e) {
                self.addressData = e.detail;
                self.getQuote();
            });

            // Place the order once we receive the checkout success event
            self.checkoutButton.addEventListener('amwalCheckoutSuccess', function (e) {
                placeAmwalOrder.execute(e.detail.orderId, self.quoteId, self.refId, self.refIdData);
            });

            // Trigger the address update so Amwal knows the shippign methods are set
            window.addEventListener('amwalRatesSet', function () {
                window.dispatchEvent(new Event('amwalAddressAck'));
            });

            // Triggered when the modal is closed
            self.checkoutButton.addEventListener('amwalDismissed', function (e) {
                $('body').trigger('processStop');
            });
        },

        /**
         * Start the express checkout flow.
         */
        startExpressCheckout: function () {
            let self = this;

            self.isClicked(true);
            $('body').trigger('processStart');

            if (!self.isMinicart) {
                if (!self.isListing) {
                    self.updateOrderedQty();
                }
                self.updateOrderedPrice();
            }
            self.checkAmount()
            if (self.isConfigurable === true) {
                self.updateSelectedConfigurableOptions();
            }
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
        updateOrderedPrice: function()  {
            this.orderedAmount = this.orderedQty * this.productPrice;
        },

        /**
         * Validates the amount and updates it if needed
         */
        checkAmount: function () {
            let self = this,
                setAmount = parseFloat(self.$checkoutButton.attr('amount')),
                actualAmount = parseFloat(this.orderedAmount);
            if (setAmount !== actualAmount) {
                self.$checkoutButton.attr('amount', actualAmount);
            }
        },

        /**
         * Observes changes to the amount attribute and updates the checkout button if needed
         * @param checkoutButton
         * @param $checkoutButton
         */
        observeAmount: function (checkoutButton, $checkoutButton) {
            let self = this,
                amountObserver = new MutationObserver(function(mutations) {
                    self.checkAmount(self.$checkoutButton);
                });

            amountObserver.observe(self.checkoutButton, {
                attributes: true,
                attributeFilter: ['amount']
            });
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
        setClickable(isClickable) {
            let self = this;
            if (!isClickable) {
                self.isClickable = false;
                self.$checkoutButton.attr('disabled', 'disabled');
            }
            if (isClickable) {
                self.isClickable = true;
                self.$checkoutButton.attr('disabled', false);
            }
        },
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
        getQuote() {
            let self = this,
                getQuoteEndpoint = urlBuilder.build('rest/V1/amwal/get-quote'),
                payload = self.getOrderData();

            payload.address_data = self.addressData;
            payload.ref_id = self.refId;
            payload.ref_id_data = self.refIdData;

            if (self.quoteId !== null) {
                payload.quote_id = self.quoteId;
            }

            $.ajax({
                url: getQuoteEndpoint,
                type: 'POST',
                data: JSON.stringify(payload),
                global: true,
                contentType: 'application/json',
                success: function (response) {
                    self.quoteId = response[0].quote_id;
                    self.orderedAmount = response[0].amount;

                    let amwalRates = [];

                    $.each(response[0].available_rates, function(rateCode, rateData) {
                        amwalRates.push({
                            'id': rateCode,
                            'label': rateData.carrier_title,
                            'price': rateData.price
                        });
                    });

                    self.$checkoutButton.attr('shipping-methods', JSON.stringify(amwalRates));
                    self.checkoutButton.shippingMethods = amwalRates;
                    window.dispatchEvent( new Event('amwalRatesSet') );
                },
                error: function (response) {
                    let message = self.getDefaultErrorMessage();
                    if (typeof response.responseJSON !== undefined && typeof response.responseJSON.message !== undefined) {
                        message = response.responseJSON.message;
                    }
                    customerData.set('messages', {
                        'messages': [{
                            'type': 'error',
                            'text': message
                        }]
                    });
                }
            });
        },
        getOrderData() {
            let orderItems = [];

            if (!this.quoteId) {
                orderItems.push({
                    'product_id': this.productId,
                    'configured_product_id': this.configuredProductId,
                    'selected_configurable_options': this.selectedConfigurableOptions,
                    'product_price': this.productPrice,
                    'qty': this.orderedQty
                });
            }
            return {
                'order_items': orderItems
            };
        },
        getDefaultErrorMessage: function() {
            return $.mage.__('Something went wrong while placing your order.');
        }
    });
});
