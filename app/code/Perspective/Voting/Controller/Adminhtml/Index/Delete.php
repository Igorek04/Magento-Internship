<?php
namespace Perspective\Voting\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Perspective\Voting\Model\Voting as Model;
use Perspective\Voting\Model\ResourceModel\Voting as ResourceModel;

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
     * @return  bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Perspective_Voting::index_delete');
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('voting_id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->model;
                $this->resourceModel->load($model, $id);
                $this->resourceModel->delete($model);

                $this->messageManager->addSuccessMessage(__('Record deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('Record does not exist.'));
        return $resultRedirect->setPath('*/*/');
    }
}
