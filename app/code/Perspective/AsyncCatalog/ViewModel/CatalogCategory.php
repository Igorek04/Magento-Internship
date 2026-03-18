<?php

namespace Perspective\AsyncCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;

class CatalogCategory implements ArgumentInterface
{
    protected $request;
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }
    public function getCategoryId()
    {
        $categoryId = $this->request->getParam('id');
        if (!$categoryId) {
            $categoryId = 0;
        }
        return $categoryId;
    }

}
