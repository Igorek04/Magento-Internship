<?php
namespace Perspective\CustomerQuestions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\CustomerQuestions\Model\ResourceModel\Question\CollectionFactory;
use Perspective\CustomerQuestions\Model\Source\Status;

class Data extends AbstractHelper
{
    protected $request;
    protected $questionCollectionFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        RequestInterface $request,
        CollectionFactory $questionCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getTabTitle()
    {
        $productId = (int)$this->request->getParam('id');
        $storeId = $this->storeManager->getStore()->getId();

        if (!$productId) {
            return __('Questions');
        }

        $collection = $this->questionCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('status', Status::STATUS_APPROVED);

        $count = $collection->getSize();

        $title = __('Questions');
        if ($count > 0) {
            $title = __('Questions (%1)', $count);
        }

        return $title;
    }
}
