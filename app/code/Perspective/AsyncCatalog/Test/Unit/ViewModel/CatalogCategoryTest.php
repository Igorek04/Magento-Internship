<?php

namespace Perspective\AsyncCatalog\Test\Unit\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Perspective\AsyncCatalog\Service\StorefrontConfig;
use Perspective\AsyncCatalog\Service\SwatchConfig;
use Perspective\AsyncCatalog\ViewModel\CatalogCategory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogCategoryTest extends TestCase
{
    private RequestInterface|MockObject $request;
    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private StorefrontConfig|MockObject $storefrontConfig;
    private SwatchConfig|MockObject $swatchConfig;
    private CatalogCategory $viewModel;

    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->storefrontConfig = $this->createMock(StorefrontConfig::class);
        $this->swatchConfig = $this->createMock(SwatchConfig::class);

        $this->viewModel = new CatalogCategory(
            $this->request,
            $this->categoryRepository,
            $this->storefrontConfig,
            $this->swatchConfig
        );
    }

    /**
     * @dataProvider getCategoryConfigDataProvider
     */
    public function testGetCategoryConfig(
        string $categoryId,
        ?string $displayMode,
        int $childrenCount,
        bool $isAnchor,
        string $expectedDisplayMode,
        bool $expectedHasChildCategories
    ): void {
        $this->request->method('getParam')
            ->with('id')
            ->willReturn($categoryId);

        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDisplayMode', 'getChildrenCount'])
            ->addMethods(['getIsAnchor'])
            ->getMock();

        $category->method('getDisplayMode')->willReturn($displayMode);
        $category->method('getChildrenCount')->willReturn($childrenCount);
        $category->method('getIsAnchor')->willReturn($isAnchor);

        $this->categoryRepository->method('get')
            ->with($categoryId)
            ->willReturn($category);

        $result = $this->viewModel->getCategoryConfig();

        $this->assertEquals($categoryId, $result['categoryId']);
        $this->assertEquals($expectedDisplayMode, $result['displayMode']);
        $this->assertEquals($expectedHasChildCategories, $result['hasChildCategories']);
        $this->assertEquals($isAnchor, $result['isAnchor']);
        $this->assertEquals(base64_encode($categoryId), $result['categoryUid']);
    }

    public static function getCategoryConfigDataProvider(): array
    {
        return [
            'fallback display mode with children' => [
                'categoryId' => '12',
                'displayMode' => null,
                'childrenCount' => 3,
                'isAnchor' => true,
                'expectedDisplayMode' => Category::DM_PRODUCT,
                'expectedHasChildCategories' => true,
            ],
            'cms only mode without children' => [
                'categoryId' => '15',
                'displayMode' => Category::DM_PAGE,
                'childrenCount' => 0,
                'isAnchor' => false,
                'expectedDisplayMode' => Category::DM_PAGE,
                'expectedHasChildCategories' => false,
            ],
        ];
    }
}

