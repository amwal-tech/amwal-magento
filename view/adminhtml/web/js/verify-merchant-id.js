define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('amwal.verifyMerchantId', {
        options: {
            baseUrl: '',
            merchantId: '',
            elementId: '',
            successText: '',
            failedText: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._verify, this)
            });
        },

        /**
         * Check the merchant ID using the Amwal API endpoint
         */
        _verify: function () {
            var result = this.options.failedText,
                element =  $('#' + this.options.elementId),
                self = this,
                msg = '',
                merchantId = $('#payment_us_amwal_payments_amwal_payments_merchant_merchant_id').val();

            element.removeClass('success').addClass('fail');

            $.ajax({
                url: this.options.baseUrl + merchantId,
                type: 'GET',
                showLoader: true
            }).done(function (response) {
                if (response.valid === true) {
                    element.removeClass('fail').addClass('success');
                    result = self.options.successText;
                }
            }).always(function () {
                $('#' + self.options.elementId + '_result').text(result);
            });
        }
    });

    return $.amwal.verifyMerchantId
});
