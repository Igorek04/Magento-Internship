<?php

namespace Perspective\CustomerQuestions\Model\ResourceModel\Answer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\CustomerQuestions\Model\Answer as Model;
use Perspective\CustomerQuestions\Model\ResourceModel\Answer as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_answer_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
