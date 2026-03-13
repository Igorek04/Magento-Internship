<?php
namespace Perspective\Voting\Service;

use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory as VotingCollectionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Service\CacheManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
class ActiveWinners
{
    protected $winners;

    /**
     * @var VotingCollectionFactory
     */
    protected $votingCollectionFactory;
    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;
    /**
     * @var ConfigData
     */
    protected $configDataService;
    /**
     * @var CacheManager
     */
    protected $cacheManager;
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param VotingCollectionFactory $votingCollectionFactory
     * @param ConfigData $configDataService
     * @param CacheManager $cacheManager
     * @param DateTime $dateTime
     * @param OptionCollectionFactory $optionCollectionFactory
     */
    public function __construct(
        VotingCollectionFactory $votingCollectionFactory,
        ConfigData $configDataService,
        CacheManager $cacheManager,
        DateTime $dateTime,
        OptionCollectionFactory $optionCollectionFactory
    ) {
        $this->votingCollectionFactory = $votingCollectionFactory;
        $this->configDataService = $configDataService;
        $this->cacheManager = $cacheManager;
        $this->dateTime = $dateTime;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Get products that currently have active winner discounts
     *
     * @return array
     */
    public function getActiveWinnerIds(): array
    {
        //get from php-request cache
        if ($this->winners !== null) {
            return $this->winners;
        }

        //get from magento cache
        $cachedWinners = $this->cacheManager->getWinnersCache();
        if ($cachedWinners !== null) {
            $this->winners = $cachedWinners;
            return $this->winners;
        }

        //get limited date(after that, finished voting winners not active)
        $currentTime = $this->dateTime->gmtTimestamp();
        $discountDurationHours = $this->configDataService->getDiscountDuration();
        $thresholdTimestamp = $currentTime - ($discountDurationHours * 3600);
        $thresholdDate = date('Y-m-d H:i:s', $thresholdTimestamp);

        //get active winner options
        $votingCollection = $this->votingCollectionFactory->create()
            ->addFieldToFilter('is_finished', 1)
            ->addFieldToFilter('finished_at', ['gt' => $thresholdDate]);
        $winnerOptionIds = $votingCollection->getColumnValues('winner_option_id');

        //get product ids from options
        $optionCollection = $this->optionCollectionFactory->create()
            ->addFieldToFilter('option_id', ['in' => $winnerOptionIds])
            ->addFieldToFilter('product_id', ['gt' => 0]);

        $winners = [];
        foreach ($optionCollection as $option) {
            $winners[$option->getProductId()] = $option->getDiscountPercent();
        }

        $this->winners = $winners;
        $this->cacheManager->saveWinnersCache($winners);
        return $this->winners;
    }

    /**
     * Check if a specific product currently has a winner discount
     *
     * @param int $productId
     * @return bool
     */
    public function isWinner(int $productId): bool
    {
        $winnerIds = $this->getActiveWinnerIds();
        return array_key_exists($productId, $winnerIds);
    }

    /**
     * Generate the HTML for the discount label using a template from the config
     *
     * @param $productId
     * @return string
     */
    public function getWinnerLabelHtml($productId): string
    {
        $html = '';
        if ($this->configDataService->isShowDiscountLabel() && $this->isWinner($productId)) {
            $winners = $this->getActiveWinnerIds();

            $template = $this->configDataService->getDiscountLabelTemplate();
            $discount = $winners[$productId];
            $html = str_replace('{{percent}}', $discount . '%', $template);
        }
        return htmlspecialchars_decode($html);
    }


    /**
     * Calculate the total discount amount for all winner products in a quote or order
     *
     * @param $entity
     * @return float|int
     */
    public function getOrderWinnersDiscount($entity)
    {
        $winners = $this->getActiveWinnerIds();

        $items = $entity->getAllVisibleItems();

        $totalDiscount = 0;
        foreach ($items as $item) {
            $productId = $item->getProductId();

            if (isset($winners[$productId])) {
                $itemPrice = $item->getBasePrice();
                $qty = $item->getQty();
                if (!$qty) {
                    $qty = $item->getQtyOrdered();
                }

                $discountPercent = $winners[$productId];
                $itemDiscount = ($itemPrice * $qty) * ($discountPercent / 100);
                $totalDiscount += $itemDiscount;
            }
        }
        return -$totalDiscount;
    }
}
