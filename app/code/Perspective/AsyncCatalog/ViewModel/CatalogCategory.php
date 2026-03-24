<?php

namespace Perspective\AsyncCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class CatalogCategory implements ArgumentInterface
{
    protected $request;
    protected $scopeConfig;
    protected $catalogConfig;
    protected $storeManager;
    protected $categoryRepository;
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        CatalogConfig $catalogConfig,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->catalogConfig = $catalogConfig;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }
    public function getCategoryId()
    {
        $categoryId = $this->request->getParam('id');
        if (!$categoryId) {
            $categoryId = 0;
        }
        return $categoryId;
    }

    public function getCategoryConfig()
    {
        $categoryId = $this->getCategoryId();
        $category = $this->categoryRepository->get($categoryId);

        return [
          'isAnchor' => (bool)$category->getIsAnchor(),
        ];
    }

    public function getModuleConfig()
    {
        return [
            'moduleEnabled' => $this->scopeConfig->getValue('perspective_async_catalog/general_settings/enabled'),
            'filtrationMode' => $this->scopeConfig->getValue('perspective_async_catalog/general_settings/catalog_filter_auto_update'),
            'lazyloadMode' => $this->scopeConfig->getValue('perspective_async_catalog/general_settings/catalog_product_lazyload')
        ];
    }

    public function getPageConfig()
    {

        $sortList = [];
        $rawSortList = $this->catalogConfig->getAttributeUsedForSortByArray();

        foreach ($rawSortList as $code => $label) {
            $sortList[] = [
                'value' => (string)$code,
                'label' => (string)$label
            ];
        }

        $listMode = $this->scopeConfig->getValue('catalog/frontend/list_mode');
        $modesArray = explode('-', $listMode ?? '');
        $availableModes = [];
        foreach ($modesArray as $mode) {
            $availableModes[] = [
                'code'  => $mode,
                'label' => ($mode === 'grid') ? (string)__('Grid') : (string)__('List')
            ];
        }


        return [
            'availableModes' => $availableModes,
            'currentMode'    => $modesArray[0] ?? 'grid',
            'gridPerPageValues'  => explode(',', $this->scopeConfig->getValue('catalog/frontend/grid_per_page_values') ?? ''),
            'gridPerPageDefault' => $this->scopeConfig->getValue('catalog/frontend/grid_per_page'),
            'listPerPageValues'  => explode(',', $this->scopeConfig->getValue('catalog/frontend/list_per_page_values') ?? ''),
            'listPerPageDefault' => $this->scopeConfig->getValue('catalog/frontend/list_per_page'),
            'defaultSortBy'      => $this->scopeConfig->getValue('catalog/frontend/default_sort_by'),
            'availableSortList' => $sortList,
        ];
    }

    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

}
