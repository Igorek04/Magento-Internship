<?php

namespace Perspective\AsyncCatalog\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;


class StorefrontConfig
{
    protected $storeManager;
    protected $scopeConfig;
    protected $catalogConfig;
    protected $assetRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CatalogConfig $catalogConfig,
        Repository $assetRepository,
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->catalogConfig = $catalogConfig;
        $this->assetRepository = $assetRepository;
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

    public function getLoaderImg()
    {
        return $this->assetRepository->getUrl('images/loader-2.gif');
    }

    public function getStoreConfig()
    {
        return [
            'currencyCode' => $this->storeManager->getStore()->getCurrentCurrency()->getCode()
        ];
    }

}
