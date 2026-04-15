<?php

namespace Perspective\CustomerQuestions\Model\ResourceModel\Like;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\CustomerQuestions\Model\Like as Model;
use Perspective\CustomerQuestions\Model\ResourceModel\Like as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_like_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
