<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Question;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Perspective\CustomerQuestions\Model\QuestionFactory;

class Save extends Action
{
    protected $adminSession;
    protected $questionFactory;

    public function __construct(
        Action\Context $context,
        Session $adminSession,
        QuestionFactory $questionFactory
    ) {
        parent::__construct($context);
        $this->adminSession = $adminSession;
        $this->questionFactory = $questionFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');

        if ($data) {
            try {
                $model = $this->questionFactory->create();
                if ($id) {
                    $model->load($id);
                }

                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('The question has been saved.'));
                $this->_getSession()->unsetData('customer_question_form_data');

                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return $resultRedirect->setPath('*/*/add');
                    }
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');

            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (!$id) {
                    $this->_getSession()->setData('customer_question_form_data', $data);
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
