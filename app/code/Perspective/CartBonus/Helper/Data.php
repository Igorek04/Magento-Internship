<?php
namespace Perspective\CartBonus\Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $productRepository;
    public function __construct(
        ProductRepositoryInterface $productRepository,
    ) {
        $this->productRepository = $productRepository;
    }

    public function stringToArray($value)
    {
        return array_map('intval', explode(',', $value));
    }

    public function getCategoryIdsByProductSku($sku): array
    {
        $product = $this->productRepository->get($sku);
        return $product->getCategoryIds();
    }
}
