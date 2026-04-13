<?php
namespace Perspective\BarberServices\Service\Create;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class Category
{
    protected $categoryFactory;
    protected $categoryRepository;
    protected $collectionFactory;

    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(array $data): void
    {
        $parts = explode('/', $data['path']);
        $currentParentId = 1;

        foreach ($parts as $categoryName) {
            $categoryName = trim($categoryName);
            $categoryId = $this->getCategoryIdByName($categoryName, $currentParentId);

            if (!$categoryId) {
                $categoryId = $this->createCategory($categoryName, $currentParentId, $data);
            } elseif ($categoryName === $data['name']) {
                $this->updateCategory($categoryId, $data['description']);
            }

            $currentParentId = $categoryId;
        }
    }

    private function getCategoryIdByName(string $name, int $parentId): ?int
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('name', $name)
                    ->addAttributeToFilter('parent_id', $parentId)
                    ->setPageSize(1);

        return $collection->getFirstItem()->getId();
    }

    private function createCategory(string $name, int $parentId, array $data): int
    {
        $urlKey = $this->generateUrlKey($name, $parentId);

        $category = $this->categoryFactory->create()
            ->setName($name)
            ->setParentId($parentId)
            ->setIsActive(true)
            ->setIncludeInMenu(true)
            ->setData('is_anchor', 1)
            ->setData('url_key', $urlKey)
            ->setStoreId(0);

        if ($name === $data['name']) {
            $category->setDescription($data['description']);
        }

        $this->categoryRepository->save($category);
        return $category->getId();
    }

    private function generateUrlKey(string $name, int $parentId): string
    {
        // transliterate and slugify
        $urlKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));

        // check if exists under same parent - add parent id suffix to make unique
        $existing = $this->getCategoryIdByName($name, $parentId);
        if ($existing) {
            $urlKey .= '-' . $parentId;
        }

        return $urlKey;
    }

    private function updateCategory(int $categoryId, string $description): void
    {
        $category = $this->categoryRepository->get($categoryId);
        $category->setDescription($description);
        $this->categoryRepository->save($category);
    }

    public function getCategoryIdByPath(string $path): int
    {
        $parts = explode('/', $path);
        $parentId = 1;

        foreach ($parts as $name) {
            $name = trim($name);
            $categoryId = $this->getCategoryIdByName($name, $parentId);

            if (!$categoryId) {
                throw new LocalizedException(__("Category path '%1' is invalid. Step '%2' not found under parent ID %3.", $path, $name, $parentId));
            }
            $parentId = $categoryId;
        }

        return $parentId;
    }
}
