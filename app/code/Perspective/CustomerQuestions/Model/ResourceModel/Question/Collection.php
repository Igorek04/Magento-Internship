<?php

namespace Perspective\CustomerQuestions\Model\ResourceModel\Question;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\CustomerQuestions\Model\Question as Model;
use Perspective\CustomerQuestions\Model\ResourceModel\Question as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_question_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
