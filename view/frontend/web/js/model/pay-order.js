define([
    'jquery',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data'
],
function ($, amwalErrorHandler, urlBuilder, customerData) {
    'use strict';

    return {

        execute: function(orderId, amwalOrderId, element) {
            let self = this,
                payOrderEndpoint = urlBuilder.build('rest/V1/amwal/pay-order'),
                payload = {
                    order_id: orderId,
                    amwal_order_id: amwalOrderId,
                };

            $('body').trigger('processStart');

            return $.ajax({
                url: payOrderEndpoint,
                type: 'POST',
                data: JSON.stringify(payload),
                global: true,
                contentType: 'application/json',
                error: function (response) {
                    let message = null;
                    if (typeof response.responseJSON !== 'undefined' && typeof response.responseJSON.message !== 'undefined') {
                        message = response.responseJSON.message;
                    }

                    amwalErrorHandler.process(element, message);
                },
                always: function () {
                    $('body').trigger('processStop');
                }
            });
        }
    }
});
