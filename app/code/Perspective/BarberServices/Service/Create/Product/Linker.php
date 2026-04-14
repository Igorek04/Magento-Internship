<?php
namespace Perspective\BarberServices\Service\Create\Product;

use Exception;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Perspective\BarberServices\Service\Create\Category as CategoryService;
use Psr\Log\LoggerInterface;

class Linker
{
    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;
    /**
     * @var LinkManagementInterface
     */
    protected $linkManagement;
    /**
     * @var CategoryService
     */
    protected $categoryService;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param LinkManagementInterface $linkManagement
     * @param CategoryService $categoryService
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryLinkManagementInterface $categoryLinkManagement,
        LinkManagementInterface $linkManagement,
        CategoryService $categoryService,
        LoggerInterface $logger
    ) {
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->linkManagement = $linkManagement;
        $this->categoryService = $categoryService;
        $this->logger = $logger;
    }

    /**
     * @param $product
     * @param string $categoryPath
     * @return void
     */
    public function linkCategory($product, string $categoryPath): void
    {
        try {
            $categoryId = $this->categoryService->getCategoryIdByPath($categoryPath);
            $this->categoryLinkManagement->assignProductToCategories($product->getSku(), [(int)$categoryId]);
        } catch (Exception $e) {
            $this->logger->error(__('Failed category link: %1', $product->getSku()));
        }
    }

    /**
     * @param $product
     * @param array $childSkus
     * @return void
     */
    public function linkConfigurableChildren($product, array $childSkus): void
    {
        if (empty($childSkus)) return;

        $existing = $this->linkManagement->getChildren($product->getSku());
        $attachedSkus = [];
        foreach ($existing as $child) {
            $attachedSkus[] = $child->getSku();
        }

        $newLinks = array_diff($childSkus, $attachedSkus);
        foreach ($newLinks as $childSku) {
            try {
                $this->linkManagement->addChild($product->getSku(), $childSku);
            } catch (Exception $e) {
                $this->logger->error(__('Failed child link: %1 to %2', $childSku, $product->getSku()));
            }
        }
    }
}
