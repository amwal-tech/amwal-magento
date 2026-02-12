require(['jquery'], function ($) {
    'use strict';

    $(document).ready(function () {
        /**
         * Amwal Payments Configuration Manager
         * Handles dynamic visibility of configuration sections based on module type and other settings
         */
        const AmwalConfigManager = {
            // Configuration selectors
            selectors: {
                // Main controls
                moduleType: '#payment_us_amwal_payments_module_type',
                systemCountryValue: '#payment_us_amwal_payments_amwal_country_specific_use_system_country_settings',

                // Country-specific fields
                countryFields: [
                    '#payment_us_amwal_payments_amwal_country_specific_allowspecific',
                    '#payment_us_amwal_payments_amwal_country_specific_specificcountry',
                    '#payment_us_amwal_payments_amwal_country_specific_limit_regions'
                ],

                // Group rows (configuration sections)
                groups: {
                    apiSettings: '#row_payment_us_amwal_payments_amwal_payments_merchant',
                    visualSettings: '#row_payment_us_amwal_payments_amwal_payments_visual',
                    promotionSettings: '#row_payment_us_amwal_payments_amwal_payments_promotion',
                    addressSettings: '#row_payment_us_amwal_payments_amwal_payments_address_attributes',
                    orderSettings: '#row_payment_us_amwal_payments_amwal_payments_orders',
                    countrySettings: '#row_payment_us_amwal_payments_amwal_country_specific',
                    webhookSettings: '#row_payment_us_amwal_payments_webhook',
                    cronSettings: '#row_payment_us_amwal_payments_amwal_payments_cronjob',
                    developerSettings: '#row_payment_us_amwal_payments_amwal_payments_developer'
                }
            },

            // Module type visibility rules
            // Define which groups should be visible for each module type
            moduleTypeRules: {
                'lite': {
                    visible: ['apiSettings', 'webhookSettings', 'developerSettings'],
                    hidden: ['visualSettings', 'promotionSettings', 'addressSettings', 'orderSettings', 'countrySettings', 'cronSettings']
                },
                'pro': {
                    visible: ['apiSettings', 'visualSettings', 'promotionSettings', 'addressSettings', 'orderSettings', 'countrySettings', 'webhookSettings', 'cronSettings', 'developerSettings'],
                    hidden: []
                }
            },

            // Cache for DOM elements
            cache: {},

            /**
             * Initialize the configuration manager
             */
            init: function() {
                this.cacheElements();
                this.bindEvents();
                this.applyInitialState();
            },

            /**
             * Cache all DOM elements for better performance
             */
            cacheElements: function() {
                this.cache.$moduleType = $(this.selectors.moduleType);
                this.cache.$systemCountryValue = $(this.selectors.systemCountryValue);
                this.cache.$countryFields = $(this.selectors.countryFields.join(', '));

                // Cache all group elements
                this.cache.groups = {};
                $.each(this.selectors.groups, function(key, selector) {
                    this.cache.groups[key] = $(selector);
                }.bind(this));
            },

            /**
             * Bind event listeners
             */
            bindEvents: function() {
                // Module type change handler
                this.cache.$moduleType.on('change', this.handleModuleTypeChange.bind(this));

                // System country value change handler
                this.cache.$systemCountryValue.on('change', this.handleCountrySettingsChange.bind(this));
            },

            /**
             * Apply initial state based on current values
             */
            applyInitialState: function() {
                this.updateModuleTypeVisibility();
                this.updateCountryFieldsState();
            },

            /**
             * Handle module type change event
             */
            handleModuleTypeChange: function() {
                this.updateModuleTypeVisibility();
            },

            /**
             * Update visibility of configuration groups based on module type
             */
            updateModuleTypeVisibility: function() {
                const moduleType = this.cache.$moduleType.val();
                const rules = this.moduleTypeRules[moduleType];

                if (!rules) {
                    console.warn('Unknown module type: ' + moduleType);
                    return;
                }

                // Show visible groups with animation
                $.each(rules.visible, function(index, groupKey) {
                    const $group = this.cache.groups[groupKey];
                    if ($group && $group.length) {
                        $group.slideDown(300);
                    }
                }.bind(this));

                // Hide hidden groups with animation
                $.each(rules.hidden, function(index, groupKey) {
                    const $group = this.cache.groups[groupKey];
                    if ($group && $group.length) {
                        $group.slideUp(300);
                    }
                }.bind(this));
            },

            /**
             * Handle country settings change event
             */
            handleCountrySettingsChange: function() {
                this.updateCountryFieldsState();
            },

            /**
             * Update enabled/disabled state of country-specific fields
             */
            updateCountryFieldsState: function() {
                const useSystemValue = this.cache.$systemCountryValue.val() === '1';

                this.cache.$countryFields.each(function() {
                    const $field = $(this);
                    const $parentRow = $field.closest('tr');

                    if (useSystemValue) {
                        $field.prop('disabled', true);
                        $parentRow.addClass('disabled-field').css('opacity', '0.5');
                    } else {
                        $field.prop('disabled', false);
                        $parentRow.removeClass('disabled-field').css('opacity', '1');
                    }
                });
            },

            /**
             * Get current module type
             * @returns {string}
             */
            getCurrentModuleType: function() {
                return this.cache.$moduleType.val();
            },

            /**
             * Check if a specific group is visible for current module type
             * @param {string} groupKey
             * @returns {boolean}
             */
            isGroupVisible: function(groupKey) {
                const moduleType = this.getCurrentModuleType();
                const rules = this.moduleTypeRules[moduleType];
                return rules && rules.visible.indexOf(groupKey) !== -1;
            }
        };

        // Initialize the configuration manager
        AmwalConfigManager.init();

        // Expose to window for debugging (optional - remove in production)
        if (typeof window.AmwalConfigManager === 'undefined') {
            window.AmwalConfigManager = AmwalConfigManager;
        }
    });
});
