define(['jquery', 'uiComponent', 'ko'], function ($, Component, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Perspective_Voting/voting',
                votingData: {}
            },
            initialize: function () {
                this._super();

                this.title = ko.observable(this.votingData.title);
                this.description = ko.observable(this.votingData.description);

                this.selectedOptionId = ko.observable(null);
                this.expiryText = this.prepareExpiryText();

                this.options = ko.observableArray([]);
                this.prepareOptions();

                console.log('Voting loaded:', this.votingData);
            },

            prepareOptions: function () {
                let totalVotes = 0;

                this.votingData.options.forEach(function (option) {
                    totalVotes += option.votes;
                });

                let prepared = this.votingData.options.map((option, index) => {
                    let percent = totalVotes > 0
                        ? Math.round((option.votes / totalVotes) * 100)
                        : 0;

                    return Object.assign({}, option, {
                        percent: percent,
                        id: index
                    });
                });

                this.options(prepared);
            },

            selectOption: function (option) {
                this.selectedOptionId(option.id);
                console.log('Selected opt id', option.id);
            },

            isSelected: function (option) {
                return this.selectedOptionId() === option.id;
            },

            sendVote: function () {
                $.ajax({
                    url: '/perspective_voting/ajax/savevote',
                    type: 'POST',
                    data: {
                        voting_id: this.votingData.id,
                        option_id: this.selectedOptionId(),
                        customer_id: 0,
                        guest_hash: 'stub_hash'
                    },
                    success: function (response) {
                        console.log('Success:', response);
                    }
                });
            },

            prepareExpiryText: function () {
                if (this.votingData.management_type === 1) {
                    let date = this.votingData.auto_end_date;
                    return 'Voting ends on ' + date;
                } else {
                    return 'Voting ends on ---';
                }
            },
        });
    }
);
