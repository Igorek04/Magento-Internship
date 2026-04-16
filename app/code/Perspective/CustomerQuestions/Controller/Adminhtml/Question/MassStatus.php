<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Perspective\CustomerQuestions\Model\ResourceModel\Question\CollectionFactory;
use Perspective\CustomerQuestions\Model\ResourceModel\Question as QuestionResourceModel;

class MassStatus extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $questionResourceModel;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        QuestionResourceModel $questionResourceModel
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->questionResourceModel = $questionResourceModel;
    }

    public function execute()
    {
        try {
            $status = $this->getRequest()->getParam('status');
            $collection = $this->collectionFactory->create();
            $collection = $this->filter->getCollection($collection);

            $excluded = $this->getRequest()->getParam('excluded');

            if ($collection->getSize() === 0 && $excluded === 'false') {
                $collection = $this->collectionFactory->create();
            }

            $updatedCount = 0;
            foreach ($collection as $model) {
                $model->setStatus($status);
                $this->questionResourceModel->save($model);
                $updatedCount++;
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $updatedCount)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
