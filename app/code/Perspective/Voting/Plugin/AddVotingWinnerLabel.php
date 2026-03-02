<?php
namespace Perspective\Voting\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Perspective\Voting\Service\ActiveWinners;

class AddVotingWinnerLabel
{
    protected $activeWinnersService;
    public function __construct(
        ActiveWinners $activeWinnersService
    ) {
        $this->activeWinnersService = $activeWinnersService;
    }

    public function afterGetProductDetailsHtml(AbstractProduct $subject, $result, Product $product)
    {
        $customHtml = $this->activeWinnersService->getWinnerLabelHtml($product->getId());
        return $result . $customHtml;
    }
}
