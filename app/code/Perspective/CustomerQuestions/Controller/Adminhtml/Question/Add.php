<?php
namespace Perspective\CustomerQuestions\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Add extends Action
{
    /**
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Add New Question'));
        return $resultPage;
    }
}
