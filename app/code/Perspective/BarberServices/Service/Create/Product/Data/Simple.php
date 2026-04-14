<?php
namespace Perspective\BarberServices\Service\Create\Product\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Perspective\BarberServices\Service\Create\Attribute as AttributeService;

class Simple
{
    /**
     * @var AttributeService
     */
    protected $attributeService;

    /**
     * @param AttributeService $attributeService
     */
    public function __construct(
        AttributeService $attributeService
    ) {
        $this->attributeService = $attributeService;
    }

    /**
     * Set simple product data
     *
     * @param ProductInterface $product
     * @param array $data
     * @param int $setId
     * @return void
     */
    public function setSimpleProductData(ProductInterface $product, array $data, int $setId): void
    {
        $product->setPrice($data['price']);
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->setStockData(['use_config_manage_stock' => 0, 'is_in_stock' => 1, 'qty' => 999]);

        foreach ($this->attributeService->getAttributesBySetId($setId) as $code => $id) {
            if (!empty($data[$code])) {
                $optionId = $this->attributeService->getOptionIdByLabel($code, $data[$code]);
                if ($optionId !== null) {
                    $product->setData($code, $optionId);
                }
            }
        }
    }
}
