<?php
namespace Perspective\AsyncCatalog\Service;

use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class SwatchConfig
{
    /**
     * @var SwatchHelper
     */
    protected $swatchHelper;
    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param SwatchHelper $swatchHelper
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        SwatchHelper $swatchHelper,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return array
     */
    public function getSwatchConfig()
    {
        $result = [];

        $collection = $this->attributeCollectionFactory->create();
        $collection->addIsFilterableFilter();

        foreach ($collection as $attribute) {
            if (!$this->swatchHelper->isSwatchAttribute($attribute)) {
                continue;
            }

            $attributeCode = $attribute->getAttributeCode();
            $attributeId = $attribute->getAttributeId();
            $options = [];

            foreach ($attribute->getOptions() as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $optionId = $option->getValue();
                $swatchData = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);

                if (isset($swatchData[$optionId])) {
                    $swatch = $swatchData[$optionId];
                    $options[$optionId] = [
                        'type' => (int)$swatch['type'],
                        'value' => $swatch['value'],
                        'label' => $option->getLabel()
                    ];
                }
            }

            if (!empty($options)) {
                $result[$attributeCode] = [
                    'attribute_id' => $attributeId,
                    'options' => $options
                ];
            }
        }

        return $result;
    }
}
