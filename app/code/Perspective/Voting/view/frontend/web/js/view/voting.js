define(['jquery', 'uiComponent', 'ko'], function ($, Component, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Perspective_Voting/voting',
                votingId: 0
            },
            initialize: function () {
                this._super();
                console.log('Voting ID loaded:', this.votingId);
            }
        });
    }
);
