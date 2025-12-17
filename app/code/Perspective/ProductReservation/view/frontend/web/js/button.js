define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "reservation_form"
], function ($, modal) {
    "use strict";

    //open modal form
    return function () {
        $('#reservation-button').on('click', function () {
            $.ajax({
                url: 'perspectivereservation/reservation/form',
                type: 'GET',
                success: function (html) {
                    $('#reservation-modal').html(html);
                    modal({
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: 'Reservation',
                        buttons: []
                    }, $('#reservation-modal'));

                    $('#reservation-modal').modal('openModal');
                        //load form.js after modal open
                    require(['reservation_form'], function(form){
                        form();
                    });
                }
            });
        });
    };
});
