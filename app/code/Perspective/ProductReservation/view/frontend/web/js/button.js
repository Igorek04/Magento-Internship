define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "reservation_form"
], function ($, modal) {
    "use strict";

    function loadReservationForm() {
        require(['reservation_form'], function (form) {
            form();
        });
    }

    function openReservationModal(html) {
        let $reservationModal = $('#reservation-modal');
        $reservationModal.html(html);

        modal({
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Reservation',
            buttons: []
        }, $reservationModal);

        $reservationModal.modal('openModal');
        loadReservationForm();
    }


    return function () {
        $('#reservation-button').on('click', function () {
            $.ajax({
                url: 'perspectivereservation/reservation/form',
                type: 'GET',
                success: openReservationModal
            });
        });
    };
});
