<?php

namespace Perspective\CustomerQuestions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Question extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_question_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('perspective_qa_question', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
