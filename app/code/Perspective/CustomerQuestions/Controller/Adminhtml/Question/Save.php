<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Perspective\CustomerQuestions\Model\QuestionFactory;
use Perspective\CustomerQuestions\Model\ResourceModel\Question as QuestionResourceModel;

class Save extends Action
{
    protected $questionFactory;
    protected $questionResourceModel;

    public function __construct(
        Action\Context $context,
        QuestionFactory $questionFactory,
        QuestionResourceModel $questionResourceModel
    ) {
        parent::__construct($context);
        $this->questionFactory = $questionFactory;
        $this->questionResourceModel = $questionResourceModel;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');

        if (!empty($data['general'])) {
            try {
                $model = $this->questionFactory->create();

                if ($id) {
                    $this->questionResourceModel->load($model, $id);
                }

                $model->setData($data['general']);
                $this->questionResourceModel->save($model);
                $id = $model->getId();

                $this->messageManager->addSuccessMessage(__('Question saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
