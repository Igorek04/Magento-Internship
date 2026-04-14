<?php
namespace Perspective\BarberServices\Service\Create;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Exception\NoSuchEntityException;

class Category
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return void
     */
    public function execute(array $data): void
    {
        $parts = explode('/', $data['path']);
        $currentParentId = CategoryModel::TREE_ROOT_ID;

        foreach ($parts as $categoryName) {
            $categoryName = trim($categoryName);
            $categoryId = $this->getCategoryIdByName($categoryName, $currentParentId);

            if (!$categoryId) {
                $categoryId = $this->createCategory($categoryName, $currentParentId, $data);
            } elseif ($categoryName === trim($data['name'])) {
                $this->updateCategory($categoryId, $data['description']);
            }

            $currentParentId = $categoryId;
        }
    }

    /**
     * @param string $name
     * @param int $parentId
     * @return int|null
     * @throws LocalizedException
     */
    private function getCategoryIdByName(string $name, int $parentId): ?int
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('name', $name)
                    ->addAttributeToFilter('parent_id', $parentId)
                    ->setPageSize(1);

        return $collection->getFirstItem()->getId();
    }

    /**
     * @param string $name
     * @param int $parentId
     * @param array $data
     * @return int
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
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

    /**
     * @param string $name
     * @param int $parentId
     * @return string
     * @throws LocalizedException
     */
    private function generateUrlKey(string $name, int $parentId): string
    {
        $urlKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        if ($this->getCategoryIdByName($name, $parentId)) {
            $urlKey = sprintf('%s-%s', $urlKey, $parentId);
        }
        return $urlKey;
    }

    /**
     * @param int $categoryId
     * @param string $description
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function updateCategory(int $categoryId, string $description): void
    {
        $category = $this->categoryRepository->get($categoryId);
        $category->setDescription($description);
        $this->categoryRepository->save($category);
    }

    /**
     * @param string $path
     * @return int
     * @throws LocalizedException
     */
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
