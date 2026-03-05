define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/customer-data'
    ], function ($, Component, ko, customerData ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Perspective_Voting/voting',
                votingData: {}
            },
            initialize: function () {
                this._super();
                let self = this;

                console.log('testik1');
                customerData.reload(['voting_data'], true);

                this.title = ko.observable(this.votingData.title);
                this.description = ko.observable(this.votingData.description);

                this.selectedOptionId = ko.observable(null);
                this.expiryText = this.prepareExpiryText();

                this.systemMessage = ko.observable(''); // ajax response msg
                this.messageStatus = ko.observable(true); // ajax response status (success\false)


                this.votingSection = customerData.get('voting_data');
                this.userVotedOptionId = ko.computed(function () {
                    let data = self.votingSection();
                    if (data && data.votes) {
                        return data.votes[self.votingData.id] || null;
                    }
                    if (data && data.votes) console.log('testik2:', data.votes);
                    return null;
                });

                this.userVotedOptionId.subscribe(function (votedId) {
                    if (votedId && !this.selectedOptionId()) {
                        this.selectedOptionId(votedId);
                    }
                }, this);
                if (this.userVotedOptionId()) {
                    this.selectedOptionId(this.userVotedOptionId());
                }




                this.options = ko.observableArray([]);
                this.prepareOptions();

                console.log('Voting loaded:', this.votingData);
                console.log('Voting data content:', customerData.get('voting_data')());

                this.buttonText = ko.computed(function () {
                    if (this.votingData.is_finished) {
                        return 'Finished';
                    }
                    if (this.votingData.management_type == 0 && this.votingData.manual_status == 0) {
                        return 'Inactive';
                    }
                    if (this.userVotedOptionId()) {
                        return 'Revote';
                    }
                    return 'Vote';
                }, this);

                this.isReadOnly = ko.computed(function () {
                    if (this.votingData.is_finished == 1) {
                        return true;
                    }
                    return this.votingData.management_type == 0 && this.votingData.manual_status == 0;
                }, this);

                this.isButtonDisabled = ko.computed(function () {
                    if (this.isReadOnly()) {
                        return true;
                    }
                    return !this.selectedOptionId();
                }, this);




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
                        //id: index
                    });
                });

                this.options(prepared);
            },

            selectOption: function (option) {
                this.selectedOptionId(option.option_id);
                console.log('Selected opt id', option.option_id);
            },

            isSelected: function (option) {
                return this.selectedOptionId() == option.option_id;
            },

            sendVote: function () {
                let self = this;
                $.ajax({
                    url: '/perspective_voting/ajax/savevote',
                    type: 'POST',
                    data: {
                        voting_id: this.votingData.id,
                        option_id: this.selectedOptionId(),
                    },
                    success: function (response) {

                        if (response.redirect && response.url) {
                            window.location.href = response.url;
                            return;
                        }

                        if (response.message) {
                            self.systemMessage(response.message);
                            self.messageStatus(response.success);

                            customerData.reload(['voting_data'], true);

                            setTimeout(function () {
                                self.systemMessage('');
                            }, 5000);
                        }
                    },
                    error: function () {
                        self.systemMessage('Something went wrong on the server');
                        self.messageStatus(false);
                    }
                });
            },

            prepareExpiryText: function () {
                if (this.votingData.is_finished == 1) {
                    return 'Voting ended at ' + this.votingData.finished_at;
                }
                if (this.votingData.management_type == 0 && this.votingData.manual_status == 0) {
                    return 'Voting is temporarily inactive';
                }
                if (this.votingData.management_type == 1) {
                    let date = this.votingData.auto_end_date;
                    return 'Voting ends on ' + date;
                } else {
                    return 'Voting ends on ---';
                }
            },
        });
    }
);
