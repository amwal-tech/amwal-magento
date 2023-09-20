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
    });
});
