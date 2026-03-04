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

    protected $votingCollectionFactory;
    protected $optionCollectionFactory;
    protected $configDataService;
    protected $cacheManager;
    protected $dateTime;

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




    public function getActiveWinnerIds(): array
    {
        if ($this->winners !== null) {
            return $this->winners;
        }

        $cachedWinners = $this->cacheManager->getWinnersCache();
        if ($cachedWinners !== null) {
            $this->winners = $cachedWinners;
            return $this->winners;
        }

        $currentTime = $this->dateTime->gmtTimestamp();
        $discountDurationHours = $this->configDataService->getDiscountDuration();
        $thresholdTimestamp = $currentTime - ($discountDurationHours * 3600);
        $thresholdDate = date('Y-m-d H:i:s', $thresholdTimestamp);

        $votingCollection = $this->votingCollectionFactory->create()
            ->addFieldToFilter('is_finished', 1)
            ->addFieldToFilter('finished_at', ['gt' => $thresholdDate]);
        $winnerOptionIds = $votingCollection->getColumnValues('winner_option_id');

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

    public function isWinner(int $productId): bool
    {
        $winnerIds = $this->getActiveWinnerIds();
        return array_key_exists($productId, $winnerIds);
    }

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

    // for quote\order
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
