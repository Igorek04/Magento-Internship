define([
    "jquery",
    "mage/mage",

], function ($, modal) {
    "use strict";

    return function () {
        //get simple product id
        function getSelectedProductId() {
            var swatchRenderer = $('[data-role=swatch-options]')
                .data('mage-SwatchRenderer');
            if (swatchRenderer && swatchRenderer.getProduct()) {
                return swatchRenderer.getProduct(); // simple
            }
            return $('input[name="product"]').val(); // configurable
        }

        //form validation
        var $form = $('#form-validate');
        $form.mage('validation', {});
        $('#form-validate').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            if (!$form.validation('isValid')) {
                return;
            }
            //form data
            var data = $form.serialize();
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
