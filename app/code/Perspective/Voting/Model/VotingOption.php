<?php

namespace Perspective\Voting\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\Voting\Model\ResourceModel\VotingOption as ResourceModel;

class VotingOption extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_option_model';

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
