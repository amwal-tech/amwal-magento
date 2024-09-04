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
        methodCode = 'amwal_payments',
        ApplePayMethodCode = 'amwal_payments_apple_pay',
        userAgent = navigator.userAgent.toLowerCase();

    // Check if Apple Pay is supported
    let windowSupportsApplePay = window.ApplePaySession?.canMakePayments() ?? false;

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

        if (windowSupportsApplePay && config[methodCode].isApplePayActive) {
            rendererList.push({
                type: ApplePayMethodCode,
                component: 'Amwal_Payments/js/view/payment/method-renderer/amwal-payment-apple-pay'
            });
        }
    }

    return Component.extend({});
});
