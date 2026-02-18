<?php

namespace Perspective\Voting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Voting extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('voting', 'voting_id');
        $this->_useIsObjectNew = true;
    }
}
