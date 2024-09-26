require(['jquery'], function ($) {
    $(document).ready(function () {
        var systemValue = $('#payment_us_amwal_payments_amwal_country_specific_use_system_country_settings');
        var fieldsToDisable = $('#payment_us_amwal_payments_amwal_country_specific_allowspecific, #payment_us_amwal_payments_amwal_country_specific_specificcountry, #payment_us_amwal_payments_amwal_country_specific_limit_regions');

        function toggleFieldState() {
            var isSystemValueEnabled = systemValue.val() === '1';
            fieldsToDisable.prop('disabled', isSystemValueEnabled);
        }

        toggleFieldState();

        systemValue.on('change', toggleFieldState);

        var moduleType = $('#payment_other_amwal_payments_amwal_payments_merchant_module_type');
        var moduleTypeFields = $('#row_payment_other_amwal_payments_amwal_payments_visual, #row_payment_other_amwal_payments_amwal_payments_installments, #row_payment_other_amwal_payments_amwal_payments_promotion, #row_payment_other_amwal_payments_amwal_payments_address_attributes, #row_payment_other_amwal_payments_amwal_payments_orders, #row_payment_other_amwal_payments_amwal_country_specific, #row_payment_other_amwal_payments_amwal_payments_cronjob, #row_payment_other_amwal_payments_amwal_payments_developer');

        function toggleModuleTypeFields() {
            console.log(moduleType.val());
            if (moduleType.val() !== 'pro') {
                moduleTypeFields.hide();
            } else {
                moduleTypeFields.show();
            }
        }

        toggleModuleTypeFields();

        moduleType.on('change', toggleModuleTypeFields);
    });
});
