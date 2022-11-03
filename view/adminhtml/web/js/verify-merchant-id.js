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
        merchantIdInput: 'input[name="groups[amwal_payments][groups][amwal_payments_merchant][fields][merchant_id][value]"]',
        merchantIdValidInput: 'input[name="groups[amwal_payments][groups][amwal_payments_merchant][fields][merchant_id_valid][value]"]',

        /**
         * Bind handlers to events
         */
        _create: function () {
            let self = this;

            self._on({
                'click': self._verify
            });

            // Automatically validate on input of merchant id field
            $(document).ready(function () {
                let validationTimeout;
                $(self.merchantIdInput).on('input', function () {
                    clearTimeout(validationTimeout);

                    // Only start validating if we have more than 20 characters (to prevent useless API calls)
                    if ($(this).val().length > 20) {
                        validationTimeout = setTimeout(function () {
                            self._verify();
                        }, 500)
                    }
                });
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
                merchantId = $(self.merchantIdInput).val();

            element.removeClass('success').addClass('fail');

            $.ajax({
                url: this.options.baseUrl + merchantId,
                type: 'GET',
                showLoader: true
            }).done(function (response) {
                if (response.valid === true) {
                    element.removeClass('fail').addClass('success');
                    result = self.options.successText;
                    $(self.merchantIdValidInput).val(1);
                    $(self.merchantIdValidInput).prop('value', 1);
                } else {
                    $(self.merchantIdValidInput).val(0);
                    $(self.merchantIdValidInput).prop('value', 0);
                }
            }).always(function () {
                $('#' + self.options.elementId + '_result').text(result);
            });
        }
    });

    return $.amwal.verifyMerchantId
});
