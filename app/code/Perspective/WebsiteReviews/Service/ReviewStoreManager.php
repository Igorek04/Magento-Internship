<?php

namespace Perspective\WebsiteReviews\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ReviewStoreManager
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param $review
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setWebsiteStoresToReview($review)
    {
        $currentStores = $review->getStores();
        $currentStoreId = $review->getStoreId();

        $websiteId = $this->storeManager->getStore($currentStoreId)->getWebsiteId();
        $websiteStoreIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();

        $finalStores = array_unique(array_merge($currentStores, $websiteStoreIds));

        $review->setStores($finalStores);
    }
}
