<?php

namespace Perspective\CustomerQuestions\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\CustomerQuestions\Model\ResourceModel\Answer as ResourceModel;

class Answer extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_answer_model';

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
