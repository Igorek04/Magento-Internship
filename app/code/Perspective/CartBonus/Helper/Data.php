<?php
namespace Perspective\CartBonus\Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $productRepository;
    protected $categoryRepository;
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
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

    public function getCategoryNameById($categoryId): string
    {
        return $this->categoryRepository->get($categoryId)->getName();
    }
}
