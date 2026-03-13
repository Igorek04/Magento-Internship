<?php
namespace Perspective\Voting\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Perspective\Voting\Service\ActiveWinners;

class AddVotingWinnerLabel
{
    /**
     * @var ActiveWinners
     */
    protected $activeWinnersService;

    /**
     * @param ActiveWinners $activeWinnersService
     */
    public function __construct(
        ActiveWinners $activeWinnersService
    ) {
        $this->activeWinnersService = $activeWinnersService;
    }

    /**
     * @param AbstractProduct $subject
     * @param $result
     * @param Product $product
     * @return string
     */
    public function afterGetProductDetailsHtml(AbstractProduct $subject, $result, Product $product)
    {
        $customHtml = $this->activeWinnersService->getWinnerLabelHtml($product->getId());
        return $result . $customHtml;
    }
}
