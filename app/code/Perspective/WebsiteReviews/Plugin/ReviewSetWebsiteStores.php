<?php
namespace Perspective\WebsiteReviews\Plugin;

use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\WebsiteReviews\Service\ReviewStoreManager;

class ReviewSetWebsiteStores
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ReviewStoreManager
     */
    protected $reviewStoreManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ReviewStoreManager $reviewStoreManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ReviewStoreManager $reviewStoreManager
    ) {
        $this->storeManager = $storeManager;
        $this->reviewStoreManager = $reviewStoreManager;
    }

    /**
     * @param ReviewResourceModel $subject
     * @param AbstractModel $object
     * @return void
     */
    public function beforeSave(ReviewResourceModel $subject, AbstractModel $object)
    {
        $this->reviewStoreManager->setWebsiteStoresToReview($object);
    }
}
