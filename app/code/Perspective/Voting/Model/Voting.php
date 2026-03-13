<?php
namespace Perspective\Voting\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\Voting\Model\ResourceModel\Voting as ResourceModel;

class Voting extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'voting_model';

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
