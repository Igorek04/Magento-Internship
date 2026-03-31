<?php

namespace Perspective\AsyncCatalog\Test\Integration\Observer;

use Magento\TestFramework\TestCase\AbstractController;
use Magento\Framework\View\LayoutInterface;

class AddAsyncCatalogHandlesTest extends AbstractController
{
    /**
     * Test that async category layout handle is added on category page.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture default/perspective_async_catalog/general_settings/enabled 1
     */
    public function testAsyncHandleIsAddedOnCategoryPage(): void
    {
        $this->dispatch('catalog/category/view/id/333');

        /** @var LayoutInterface $layout */
        $layout = $this->_objectManager->get(LayoutInterface::class);
        $handles = $layout->getUpdate()->getHandles();

        $this->assertContains('perspective_async_catalog_category_view', $handles);
    }

    /**
     * Test that async catalog root block is present on category page.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture default/perspective_async_catalog/general_settings/enabled 1
     */
    public function testAsyncCatalogRootBlockExistsOnCategoryPage(): void
    {
        $this->dispatch('catalog/category/view/id/333');

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get(\Magento\Framework\View\LayoutInterface::class);

        $this->assertNotFalse($layout->getBlock('async.catalog.root'));
    }

    /**
     * Test that filters placeholder block is present on layered category page.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture default/perspective_async_catalog/general_settings/enabled 1
     */
    public function testFiltersPlaceholderBlockExistsOnLayeredCategoryPage(): void
    {
        $this->dispatch('catalog/category/view/id/333');

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get(\Magento\Framework\View\LayoutInterface::class);

        $this->assertNotFalse($layout->getBlock('async.catalog.root.filters.placeholder'));
    }
}

