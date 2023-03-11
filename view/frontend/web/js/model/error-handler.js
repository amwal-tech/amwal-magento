define([
    'jquery',
    'Magento_Customer/js/customer-data'
],
function ($, customerData) {
    'use strict';

    return {

        /**
         * Process an error message and send the associated event to the Amwal modal
         * @return {String}
         */
        process: function(element, message) {
            message = message || this.getDefaultErrorMessage();

            element.dispatchEvent(new CustomEvent('amwalPrePayTriggerError', {
                detail: {
                    description: message
                }
            }));

            customerData.set('messages', {
                'messages': [{
                    'type': 'error',
                    'text': message
                }]
            });
        },

        /**
         * Return the translated default error message.
         * @return {String}
         */
        getDefaultErrorMessage: function() {
            return $.mage.__('Something went wrong while placing your order.');
        }
    }
});
