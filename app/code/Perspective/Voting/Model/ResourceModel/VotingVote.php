<?php

namespace Perspective\Voting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VotingVote extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_vote_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('voting_vote', 'vote_id');
        $this->_useIsObjectNew = true;
    }
}
