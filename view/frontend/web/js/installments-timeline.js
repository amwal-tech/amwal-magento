define([
    'jquery',
    'uiComponent',
    'placeAmwalOrder',
    'payAmwalOrder',
    'amwalErrorHandler',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'underscore',
    'mage/translate',
    'domReady!view'
],
function ($, Component, placeAmwalOrder, payAmwalOrder, amwalErrorHandler, urlBuilder, customerData, _) {
    'use strict';

    return Component.extend({
        timelineId: null,
        installmentsContainer: null,

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            let self = this;

            self.installmentsContainer = document.getElementById(self.timelineId);

            if (window.renderTimelineElement) {
                window.renderTimelineElement(self.installmentsContainer);
            }

            return self;
        },
        /**
         * Initialize installments timeline
         */
        initializeInstallmentsTimeline: function () {
            let self = this;
            const amwalInstallmentsTimeline = document.getElementById('amwal-installments-timeline');
            console.warn('amwalInstallmentsTimeline', amwalInstallmentsTimeline);
            if (amwalInstallmentsTimeline) {
                window.renderTimelineElement(amwalInstallmentsTimeline);
            }
        }
    });
});
