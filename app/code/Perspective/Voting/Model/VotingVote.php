<?php
namespace Perspective\Voting\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\Voting\Model\ResourceModel\VotingVote as ResourceModel;

class VotingVote extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_vote_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
