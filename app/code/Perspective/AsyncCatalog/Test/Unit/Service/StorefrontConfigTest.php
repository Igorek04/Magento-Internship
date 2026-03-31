<?php

namespace Perspective\AsyncCatalog\Test\Unit\Service;

use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\AsyncCatalog\Service\StorefrontConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorefrontConfigTest extends TestCase
{
    private StoreManagerInterface|MockObject $storeManager;
    private ScopeConfigInterface|MockObject $scopeConfig;
    private CatalogConfig|MockObject $catalogConfig;
    private Repository|MockObject $assetRepository;
    private StorefrontConfig $storefrontConfig;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->catalogConfig = $this->createMock(CatalogConfig::class);
        $this->assetRepository = $this->createMock(Repository::class);

        $this->storefrontConfig = new StorefrontConfig(
            $this->storeManager,
            $this->scopeConfig,
            $this->catalogConfig,
            $this->assetRepository
        );
    }

    /**
     * @dataProvider getPageConfigDataProvider
     */
    public function testGetPageConfig(
        string $listMode,
        array $sortByArray,
        array $expectedModes,
        string $expectedCurrentMode,
        array $expectedSortList
    ): void {
        $this->catalogConfig->method('getAttributeUsedForSortByArray')
            ->willReturn($sortByArray);

        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function (string $path) use ($listMode) {
                return match ($path) {
                    'catalog/frontend/list_mode' => $listMode,
                    'catalog/frontend/grid_per_page_values' => '12,24,36',
                    'catalog/frontend/grid_per_page' => '12',
                    'catalog/frontend/list_per_page_values' => '6,12,18',
                    'catalog/frontend/list_per_page' => '6',
                    'catalog/frontend/default_sort_by' => 'position',
                    default => null,
                };
            });

        $result = $this->storefrontConfig->getPageConfig();

        $this->assertEquals($expectedModes, $result['availableModes']);
        $this->assertEquals($expectedCurrentMode, $result['currentMode']);
        $this->assertEquals($expectedSortList, $result['availableSortList']);
    }

    public static function getPageConfigDataProvider(): array
    {
        return [
            'grid-list mode with sort options' => [
                'listMode' => 'grid-list',
                'sortByArray' => [
                    'position' => 'Position',
                    'name' => 'Name',
                ],
                'expectedModes' => [
                    ['code' => 'grid', 'label' => 'Grid'],
                    ['code' => 'list', 'label' => 'List'],
                ],
                'expectedCurrentMode' => 'grid',
                'expectedSortList' => [
                    ['value' => 'position', 'label' => 'Position'],
                    ['value' => 'name', 'label' => 'Name'],
                ],
            ],
            'grid mode only' => [
                'listMode' => 'grid',
                'sortByArray' => [
                    'position' => 'Position',
                ],
                'expectedModes' => [
                    ['code' => 'grid', 'label' => 'Grid'],
                ],
                'expectedCurrentMode' => 'grid',
                'expectedSortList' => [
                    ['value' => 'position', 'label' => 'Position'],
                ],
            ],
        ];
    }
}
