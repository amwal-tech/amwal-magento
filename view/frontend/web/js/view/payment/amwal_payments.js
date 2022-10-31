define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],
function (
    Component,
    rendererList
) {
    'use strict';

    let config = window.checkoutConfig.payment,
        methodCode = 'amwal_payments';

    if (config[methodCode] && config[methodCode].isActive && config[methodCode].isRegularCheckoutActive) {
        rendererList.push(
            {
                type: methodCode,
                component: 'Amwal_Payments/js/view/payment/method-renderer/amwal-payment'
            }
        );
    }

    return Component.extend({});
});
