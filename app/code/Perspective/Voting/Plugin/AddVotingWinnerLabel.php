<?php
namespace Perspective\Voting\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;

class AddVotingWinnerLabel
{
    public function afterGetProductDetailsHtml(AbstractProduct $subject, $result, Product $product)
    {
        $test = 1;
        if ($test = 1) {
            $customHtml = '<div style="background:#fff3cd; text-align:center; font-weight:bold;">
                            Voting Winner Discount
                            </div>';
            return $result . $customHtml;
        } else {
            return $result;
        }
    }
}
