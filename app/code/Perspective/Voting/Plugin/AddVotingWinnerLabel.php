<?php
namespace Perspective\Voting\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Perspective\Voting\Service\ActiveWinners;
use Perspective\Voting\Service\ConfigData;


class AddVotingWinnerLabel
{
    /**
     * @var ActiveWinners
     */
    protected $activeWinnersService;

    /**
     * @var ConfigData
     */
    protected $configDataService;

    /**
     * @param ActiveWinners $activeWinnersService
     * @param ConfigData $configDataService
     */
    public function __construct(
        ActiveWinners $activeWinnersService,
        ConfigData $configDataService
    ) {
        $this->activeWinnersService = $activeWinnersService;
        $this->configDataService = $configDataService;
    }

    /**
     * @param AbstractProduct $subject
     * @param $result
     * @param Product $product
     * @return string
     */
    public function afterGetProductDetailsHtml(AbstractProduct $subject, $result, Product $product)
    {
        $customHtml = '';
        if ($this->configDataService->isModuleEnabled()) {
            $customHtml = $this->activeWinnersService->getWinnerLabelHtml($product->getId());
        }
        return $result . $customHtml;
    }
}
