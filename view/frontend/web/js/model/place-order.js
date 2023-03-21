define([
    'jquery',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data'
],
function ($, amwalErrorHandler, urlBuilder, customerData) {
    'use strict';

    return {

        execute: function(amwalOrderId, quoteId, refId, refIdData, triggerContext, hasAmwalAddress, element) {
            let self = this,
                placeOrderEndpoint = urlBuilder.build('rest/V1/amwal/place-order'),
                payload = {
                    quote_id: quoteId,
                    amwal_order_id: amwalOrderId,
                    ref_id: refId,
                    ref_id_data: refIdData,
                    trigger_context: triggerContext,
                    has_amwal_address: hasAmwalAddress
                };

            $('body').trigger('processStart');
            return $.ajax({
                url: placeOrderEndpoint,
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
