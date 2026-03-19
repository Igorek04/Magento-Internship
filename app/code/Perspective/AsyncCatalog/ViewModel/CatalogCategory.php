<?php

namespace Perspective\AsyncCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogCategory implements ArgumentInterface
{
    protected $request;
    protected $scopeConfig;
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }
    public function getCategoryId()
    {
        $categoryId = $this->request->getParam('id');
        if (!$categoryId) {
            $categoryId = 0;
        }
        return $categoryId;
    }

    public function getPageConfig()
    {
        return [
            'listMode'           => $this->scopeConfig->getValue('catalog/frontend/list_mode'),
            'gridPerPageValues'  => explode(',', $this->scopeConfig->getValue('catalog/frontend/grid_per_page_values') ?? ''),
            'gridPerPageDefault' => $this->scopeConfig->getValue('catalog/frontend/grid_per_page'),
            'listPerPageValues'  => explode(',', $this->scopeConfig->getValue('catalog/frontend/list_per_page_values') ?? ''),
            'listPerPageDefault' => $this->scopeConfig->getValue('catalog/frontend/list_per_page'),
            'defaultSortBy'      => $this->scopeConfig->getValue('catalog/frontend/default_sort_by'),
        ];
    }

}
