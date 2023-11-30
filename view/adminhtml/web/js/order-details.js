define('Amwal_Payments/js/order-details', ['jquery', 'Magento_Ui/js/modal/modal', 'mage/translate'], function ($, modal, $t) {
    'use strict';

    return function (config) {
        // Parse response JSON
        var responseJson = JSON.parse(config.order_details);
        var amwalOrderStatus = responseJson.status !== null ? responseJson.status : '-';
        var amwalOrderFailureReason = responseJson.failure_reason;

        config.orderId = config.order_id;
        config.amwalOrderId = config.amwal_order_id;
        config.refId = responseJson.ref_id;

        // Create modal elements
        var modalContainer = $('<div>', {id: 'amwal_order_details_modal', style: 'display:none'});
        var modalContent = $('<div>', {id: 'amwal_order_details_modal_content'});

        var amwalOrderStatusHtml = '<tr id="amwal_order_status"> <th>' +  $t("Amwal Order Status") + '</th> <td><span>' + amwalOrderStatus + '</span></td> </tr>';
        if (!$('#amwal_order_status').length && amwalOrderStatus) {
            $('.order-information-table tbody').append(amwalOrderStatusHtml);
        }
        var amwalOrderPaymentMethodHtml = '<tr id="amwal_order_payment_method"> <th>' +  $t("Amwal Payment Method") + '</th> <td><span>' + responseJson.payment_method + '</span></td> </tr>';
        if (!$('#amwal_order_payment_method').length && responseJson.payment_method) {
            $('.order-information-table tbody').append(amwalOrderPaymentMethodHtml);
        }
        var amwalOrderPaymentBrandHtml = '<tr id="amwal_order_payment_brand"> <th>' +  $t("Amwal Payment Brand") + '</th> <td><span>' + responseJson.paymentBrand + '</span></td> </tr>';
        if (!$('#amwal_order_payment_brand').length && responseJson.paymentBrand) {
            $('.order-information-table tbody').append(amwalOrderPaymentBrandHtml);
        }
        var amwalOrderFailureReasonHtml = '<tr id="amwal_order_failure_reason"> <th>' +  $t("Amwal Order Failure Reason") + '</th> <td><span>' + amwalOrderFailureReason + '</span></td> </tr>';
        if (!$('#amwal_order_failure_reason').length && amwalOrderFailureReason && amwalOrderStatus === 'fail') {
            $('.order-information-table tbody').append(amwalOrderFailureReasonHtml);
        }

        // Create modal options
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Amwal Order Details',
            modalClass: 'amwal-order-details-modal',
            buttons: [],
            closed: function () {
                modalContent.empty();
            },
            modalSize: 'sm'
        };

        // Create modal instance
        var modalPopup = modal(options, modalContainer);


        $(document).on('click', '#' + config.buttonId, function () {
            // Create modal content
            var content = `
                <div class="message message-info">
                    <span class="amwal-order-status-label">${ $t('Amwal Order Status') }</span>
                    <span class="amwal-order-status-value">${amwalOrderStatus}</span>
                </div>
                <br>
                <center>
                    <h2 class="text-center">${ $t('Are you sure you want to update the order status?') }</h2>
                </center>
                <footer class="modal-footer">
                    <button class="action-secondary action-dismiss" type="button" data-role="action"><span>Cancel</span></button>
                    <button class="action-primary action-accept order-status-update" type="button" data-role="action"><span>OK</span></button>
                </footer>
            `;

            // Set modal content
            $('.amwal-order-details-modal .modal-content').html(content);
            modalPopup.openModal();
        });

        // Close modal when close button is clicked
        $(document).on('click', '.amwal-order-details-modal .modal-header .action-close, .action-dismiss', function () {
            modalContent.empty();
            modalPopup.closeModal();
        });

        // Handle order status update button click
        $(document).on('click', '.amwal-order-details-modal .modal-footer .order-status-update', function () {
            var requestData = {
                order_id: config.orderId,
                amwal_order_id: config.amwalOrderId,
                ref_id: config.refId
            };

            fetch('/rest/V1/amwal/order/status', {
                method: 'POST',
                body: JSON.stringify(requestData),
                headers: {
                    'Content-type': 'application/json; charset=UTF-8'
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    location.reload();
                })
                .catch((error) => {
                    alert($t('An error occurred while updating the order status.'));
                });
        });
    };
});
