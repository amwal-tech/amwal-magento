define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list',
    'Magento_Checkout/js/model/totals'
],
function (
    Component,
    rendererList,
    totals
) {
    'use strict';

    let config = window.checkoutConfig.payment,
        methodCode = 'amwal_payments';

    if (config[methodCode] && config[methodCode].isActive &&
        config[methodCode].isRegularCheckoutActive &&
        (parseFloat(totals.totals().base_grand_total) > 0)
    ) {
        rendererList.push(
            {
                type: methodCode,
                component: 'Amwal_Payments/js/view/payment/method-renderer/amwal-payment'
            }
        );
    }

    return Component.extend({});
});
