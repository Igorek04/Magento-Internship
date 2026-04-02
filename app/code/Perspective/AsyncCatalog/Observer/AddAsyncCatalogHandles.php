<?php
namespace Perspective\AsyncCatalog\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Perspective\AsyncCatalog\Service\StorefrontConfig;

class AddAsyncCatalogHandles implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var StorefrontConfig
     */
    protected StorefrontConfig $storefrontConfig;

    /**
     * @param RequestInterface $request
     * @param StorefrontConfig $storefrontConfig
     */
    public function __construct(
        RequestInterface $request,
        StorefrontConfig $storefrontConfig
    ) {
        $this->request = $request;
        $this->storefrontConfig = $storefrontConfig;
    }

    /**
     * replace default catalog to custom async catalog if module enabled
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Layout|null $layout */
        $layout = $observer->getData('layout');
        if (
            !$layout ||
            !$this->isModuleEnabled() ||
            !$this->isCategoryPage()
        ) {
            return;
        }

        foreach ($layout->getUpdate()->getHandles() as $handle) {
            if (strpos($handle, 'catalog_category_view') === 0) {
                $layout->getUpdate()->addHandle('perspective_async_' . $handle);
            }
        }
    }

    /**
     * @return bool
     */
    private function isModuleEnabled(): bool
    {
        $moduleConfig = $this->storefrontConfig->getModuleConfig();
        return (bool)$moduleConfig['moduleEnabled'];
    }

    /**
     * @return bool
     */
    private function isCategoryPage(): bool
    {
        return $this->request->getFullActionName() === 'catalog_category_view';
    }
}
