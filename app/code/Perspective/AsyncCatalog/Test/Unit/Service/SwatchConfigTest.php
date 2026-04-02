<?php

namespace Perspective\AsyncCatalog\Test\Unit\Service;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Perspective\AsyncCatalog\Service\SwatchConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwatchConfigTest extends TestCase
{
    private SwatchHelper|MockObject $swatchHelper;
    private CollectionFactory|MockObject $attributeCollectionFactory;
    private SwatchConfig $swatchConfig;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->swatchHelper = $this->createMock(SwatchHelper::class);
        $this->attributeCollectionFactory = $this->createMock(CollectionFactory::class);

        $this->swatchConfig = new SwatchConfig(
            $this->swatchHelper,
            $this->attributeCollectionFactory
        );
    }

    /**
     * @dataProvider getSwatchConfigDataProvider
     */
    public function testGetSwatchConfig(
        bool $isSwatchAttribute,
        string $optionValue,
        array $swatchData,
        array $expectedResult
    ): void {
        $option = $this->createMock(Option::class);
        $option->method('getValue')->willReturn($optionValue);
        $option->method('getLabel')->willReturn('Red');

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeCode', 'getAttributeId', 'getOptions'])
            ->getMock();

        $attribute->method('getAttributeCode')->willReturn('color');
        $attribute->method('getAttributeId')->willReturn(93);
        $attribute->method('getOptions')->willReturn([$option]);

        $collection = $this->createMock(Collection::class);
        $collection->method('addIsFilterableFilter')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$attribute]));

        $this->attributeCollectionFactory->method('create')->willReturn($collection);

        $this->swatchHelper->method('isSwatchAttribute')
            ->with($attribute)
            ->willReturn($isSwatchAttribute);

        $this->swatchHelper->method('getSwatchesByOptionsId')
            ->willReturn($swatchData);

        $result = $this->swatchConfig->getSwatchConfig();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array[]
     */
    public static function getSwatchConfigDataProvider(): array
    {
        return [
            'valid swatch attribute with option data' => [
                'isSwatchAttribute' => true,
                'optionValue' => '101',
                'swatchData' => [
                    '101' => [
                        'type' => 1,
                        'value' => '#ff0000',
                    ],
                ],
                'expectedResult' => [
                    'color' => [
                        'attribute_id' => 93,
                        'options' => [
                            '101' => [
                                'type' => 1,
                                'value' => '#ff0000',
                                'label' => 'Red',
                            ],
                        ],
                    ],
                ],
            ],
            'swatch attribute with empty option value' => [
                'isSwatchAttribute' => true,
                'optionValue' => '',
                'swatchData' => [],
                'expectedResult' => [],
            ],
            'non swatch attribute' => [
                'isSwatchAttribute' => false,
                'optionValue' => '101',
                'swatchData' => [
                    '101' => [
                        'type' => 1,
                        'value' => '#ff0000',
                    ],
                ],
                'expectedResult' => [],
            ],
        ];
    }
}
