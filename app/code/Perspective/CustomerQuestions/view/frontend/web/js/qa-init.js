define(['alpine'], function (Alpine) {
    'use strict';

    if (!window.Alpine) {
        window.Alpine = Alpine;
        Alpine.start();
    }

    return Alpine;
});
