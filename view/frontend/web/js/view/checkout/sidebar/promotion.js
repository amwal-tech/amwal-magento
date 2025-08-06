define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'mage/url'
], function (Component, ko, $, quote, totals, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amwal_Payments/checkout/sidebar/promotion'
        },

        initialize: function () {
            this._super();
            this.visible = ko.observable(true);
            this.widgetElement = null;

            // Bind methods to maintain context
            this.isVisible = this.isVisible.bind(this);
            this.getGrandTotal = this.getGrandTotal.bind(this);

            // Setup observables and initialize
            var self = this;
            var update = function() {
                self.updateWidget();
            };

            totals.totals.subscribe(update);
            quote.totals.subscribe(update);
            setTimeout(update, 100);

            this.fetchCurrencyRate();
            this.initWidget();
            return this;
        },

        fetchCurrencyRate: function () {
            var self = this;
            $.get(urlBuilder.build('rest/V1/amwal/currency'))
                .done(function (response) {
                    var currencyData = Array.isArray(response) ? response[0] : response;
                    var amount = currencyData && currencyData.amount ? currencyData.amount : 1;
                    if (self.widgetElement) {
                        self.updateWidget(amount);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.warn('Failed to fetch currency rate:', textStatus, errorThrown);
                });
        },

        getGrandTotal: function () {
            var quoteTotals = quote.totals() || totals.totals() || {};
            return parseFloat(quoteTotals.grand_total || 0);
        },

        initWidget: function () {
            var self = this;
            var attempts = 0;

            var checkWidget = function() {
                var widget = document.getElementById('amwal-widget');
                var hasCustomElement = typeof customElements !== 'undefined' && customElements.get('amwal-widget');
                var grandTotal = self.getGrandTotal();

                if (widget && hasCustomElement && grandTotal > 0) {
                    widget.config = {
                        installmentsCount: 12,
                        price: grandTotal,
                        currency: 'SAR'
                    };
                    widget.locale = document.documentElement.lang || 'en';
                    self.widgetElement = widget;
                    return;
                }

                if (++attempts < 50) {
                    setTimeout(checkWidget, 100);
                }
            };

            setTimeout(checkWidget, 300);
        },

        updateWidget: function (price) {
            var grandTotal = price || this.getGrandTotal();
            this.visible(grandTotal > 0);
            if (this.widgetElement) {
                this.widgetElement.config = {
                    installmentsCount: 12,
                    price: grandTotal,
                    currency: 'SAR',
                    locale: document.documentElement.lang || 'en'
                };
            }
        },

        isVisible: function () {
            return this.getGrandTotal() > 0;
        },

        destroy: function () {
            this.widgetElement = null;
            this._super();
        }
    });
});
