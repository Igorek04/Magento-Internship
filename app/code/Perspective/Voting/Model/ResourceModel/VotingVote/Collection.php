<?php

namespace Perspective\Voting\Model\ResourceModel\VotingVote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\Voting\Model\ResourceModel\VotingVote as ResourceModel;
use Perspective\Voting\Model\VotingVote as Model;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_vote_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
