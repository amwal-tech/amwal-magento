/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
], function ($) {
    'use strict';

    return {
        initialized: false,
        validators: [],

        /**
         * Inits list of validators
         */
        initialize: function () {
            if (this.initialized) {
                return;
            }

            this.initialized = true;
        },

        /**
         * Gets payment config
         *
         * @returns {Object}
         */
        getConfig: function () {
            return window.checkoutConfig.payment;
        },

        /**
         * Adds new validator
         *
         * @param {Object} validator
         */
        add: function (validator) {
            this.validators.push(validator);
        },

        /**
         * Runs pull of validators
         *
         * @param {Object} context
         * @param {Function} successCallback
         * @param {Function} errorCallback
         */
        validate: function (context, successCallback, errorCallback) {
            var self = this,
                deferred;

            self.initialize();

            // no available validators
            if (!self.validators.length) {
                successCallback();

                return;
            }

            // get list of deferred validators
            deferred = $.map(self.validators, function (current) {
                return current.validate(context);
            });

            $.when.apply($, deferred)
                .done(function () {
                    successCallback();
                }).fail(function (error) {
                    errorCallback(error);
                });
        }
    };
});
