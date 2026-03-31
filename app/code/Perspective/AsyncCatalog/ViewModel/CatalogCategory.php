<?php
namespace Perspective\AsyncCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Perspective\AsyncCatalog\Service\StorefrontConfig;
use Perspective\AsyncCatalog\Service\SwatchConfig;

class CatalogCategory implements ArgumentInterface
{
    protected $request;
    protected $categoryRepository;
    protected $swatchConfig;
    protected $storefrontConfig;

    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        StoreFrontConfig $storefrontConfig,
        SwatchConfig $swatchConfig

    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->storefrontConfig = $storefrontConfig;
        $this->swatchConfig = $swatchConfig;
    }
    public function getCategoryId(): string
    {
        $categoryId = $this->request->getParam('id');
        if (!$categoryId) {
            $categoryId = 0;
        }
        return (string)$categoryId;
    }

    public function getCategoryConfig(): array
    {
        $categoryId = $this->getCategoryId();
        $category = $this->categoryRepository->get($categoryId);

        //manual set default value, because if that not changed it was return null
        $displayMode = $category->getDisplayMode() ?: Category::DM_PRODUCT;

        return [
            'categoryId' => $categoryId,
            'isAnchor' => (bool)$category->getIsAnchor(),
            'displayMode' => $displayMode,
            'categoryUid' => base64_encode($categoryId),
            'hasChildCategories' => $category->getChildrenCount() > 0
        ];
    }

    public function getConfig(): array
    {
        return [
            'categoryConfig' => $this->getCategoryConfig(),
            'pageConfig' => $this->storefrontConfig->getPageConfig(),
            'moduleConfig' => $this->storefrontConfig->getModuleConfig(),
            'loaderIcon' => $this->storefrontConfig->getLoaderImg(),
            'storeConfig' => $this->storefrontConfig->getStoreConfig(),
            'swatchConfig' => $this->swatchConfig->getSwatchConfig(),
        ];
    }
}
