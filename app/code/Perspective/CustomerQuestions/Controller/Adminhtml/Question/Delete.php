<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Question;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Perspective\CustomerQuestions\Model\Question as Model;
use Perspective\CustomerQuestions\Model\ResourceModel\Question as ResourceModel;

class Delete extends Action
{
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @param Context $context
     * @param Model $model
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        Context $context,
        Model $model,
        ResourceModel $resourceModel
    ) {
        parent::__construct($context);
        $this->model = $model;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('entity_id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->model;
                $this->resourceModel->load($model, $id);
                $this->resourceModel->delete($model);

                $this->messageManager->addSuccessMessage(__('Question deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('Question does not exist.'));
        return $resultRedirect->setPath('*/*/');
    }
}
