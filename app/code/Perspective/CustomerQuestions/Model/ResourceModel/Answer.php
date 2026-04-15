<?php

namespace Perspective\CustomerQuestions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Answer extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'perspective_qa_answer_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('perspective_qa_answer', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
