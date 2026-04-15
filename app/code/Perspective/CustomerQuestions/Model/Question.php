<?php

namespace Perspective\CustomerQuestions\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\CustomerQuestions\Model\ResourceModel\Question as ResourceModel;

class Question extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_question_model';

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
