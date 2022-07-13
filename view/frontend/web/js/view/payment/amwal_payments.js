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

        let config = window.checkoutConfig.payment;
        let iframe = 'amwal_iframe';
        
        if (config[iframe].isActive) {
            rendererList.push(
                {
                    type: iframe,
                    component: 'Amwal_Payments/js/view/payment/method-renderer/iframe-form'
                }
            );
        }

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
