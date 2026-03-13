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
                customerData.reload(['voting_data'], true);

                this.title = ko.observable(this.votingData.title);
                this.description = ko.observable(this.votingData.description);

                this.selectedOptionId = ko.observable(null);
                this.expiryText = this.prepareExpiryText(); // voting end date label

                this.systemMessage = ko.observable(''); // ajax response msg
                this.messageStatus = ko.observable(true); // ajax response status (success\false)

                this.votingSection = customerData.get('voting_data'); // loaded user votes from customer section

                this.userVotedOptionId = ko.computed(function () { // user voted option from customer section
                    let data = self.votingSection();
                    if (data && data.votes) {
                        return data.votes[self.votingData.id] || null;
                    }
                    return null;
                });

                // pre-select option if user have voted option
                this.userVotedOptionId.subscribe(function (votedId) {
                    if (votedId && !this.selectedOptionId()) {
                        this.selectedOptionId(votedId);
                    }
                }, this);
                if (this.userVotedOptionId()) {
                    this.selectedOptionId(this.userVotedOptionId());
                }

                // calculate percentage for options
                this.options = ko.observableArray([]);
                this.prepareOptions();

                // dynamic button label
                this.buttonText = ko.computed(function () {
                    if (this.votingData.is_finished) {
                        return $.__('Finished');
                    }
                    if (this.votingData.management_type == 0 && this.votingData.manual_status == 0) {
                        return $.__('Inactive');
                    }
                    if (this.userVotedOptionId()) {
                        return $.__('Revote');
                    }
                    return $.__('Vote');
                }, this);

                // calculated variable to lock voting if its finished or manually disabled
                this.isReadOnly = ko.computed(function () {
                    if (this.votingData.is_finished == 1) {
                        return true;
                    }
                    return this.votingData.management_type == 0 && this.votingData.manual_status == 0;
                }, this);

                // calculated variable to disable button
                this.isButtonDisabled = ko.computed(function () {
                    if (this.isReadOnly()) {
                        return true;
                    }
                    return !this.selectedOptionId();
                }, this);
            },

            // add percentage field to options
            prepareOptions: function () {
                let totalVotes = 0;

                this.votingData.options.forEach(function (option) {
                    totalVotes += option.votes;
                });

                let prepared = this.votingData.options.map((option) => {
                    let percent = totalVotes > 0
                        ? Math.round((option.votes / totalVotes) * 100)
                        : 0;

                    return Object.assign({}, option, {
                        percent: percent,
                    });
                });

                this.options(prepared);
            },

            //set current selected option ID on click
            selectOption: function (option) {
                this.selectedOptionId(option.option_id);
            },

            //check if given option = currently selected
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
                        //redirect guest if needed by response
                        if (response.redirect && response.url) {
                            window.location.href = response.url;
                            return;
                        }

                        //set system msg
                        if (response.message) {
                            self.systemMessage(response.message); //msg
                            self.messageStatus(response.success); //status for CSS

                            customerData.reload(['voting_data'], true);

                            //cleaning msg after time out
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

            // voting end date label data
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
