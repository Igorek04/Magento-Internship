<?php

namespace Perspective\Voting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VotingOption extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_option_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('voting_option', 'option_id');
        $this->_useIsObjectNew = true;
    }
}
