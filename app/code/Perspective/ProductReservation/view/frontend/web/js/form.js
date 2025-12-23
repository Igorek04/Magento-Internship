define([
    "jquery",
    "mage/mage",
], function ($) {
    "use strict";

    return function () {
        //get simple product id
        function getSelectedProductId() {
            let swatchRenderer = $('[data-role=swatch-options]').data('mage-SwatchRenderer');
            let selectedProductId = $('input[name="product"]').val(); // configurable

            if(swatchRenderer) {
                let simpleProductId = swatchRenderer.getProduct();
                if (simpleProductId) {
                    selectedProductId = simpleProductId; // simple
                }
            }
            return selectedProductId;
        }

        //form validation
        let $form = $('#form-validate');
        $form.mage('validation', {});
        $form.on('submit', function (e) {
            e.preventDefault();
            let $form = $(this);
            if (!$form.validation('isValid')) {
                return;
            }
            //form data
            let data = $form.serialize();
            data += '&qty=' + $('#qty').val();
            data += '&product_id=' + getSelectedProductId();

            //send data to controller for create order
            $.ajax({
                url: 'perspectivereservation/reservation/order',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (!response.success) {
                        $('.reservation-messages').html(
                            '<div class="message error">' +
                            response.message +
                            '</div>'
                        );
                        return;
                    }
                    console.log(response);
                    $('#reservation-modal').modal('closeModal');
                },
                error: function () {
                    console.log('Error');
                }
            });
        });
    }
});
