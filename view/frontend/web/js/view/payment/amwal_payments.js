define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'amwal_payments',
                component: 'Amwal_Payments/js/view/payment/method-renderer/amwal-payment'
            }
        );

        return Component.extend({});
    }
);

