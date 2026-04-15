<?php

namespace Perspective\BarberServices\Service;

use Exception;
use Perspective\BarberServices\Service\Create\Attribute as AttributeService;
use Perspective\BarberServices\Service\Create\Product as ProductService;
use Perspective\BarberServices\Service\Create\Category as CategoryService;
use Perspective\BarberServices\Service\File\Directory as DirectoryService;
use Perspective\BarberServices\Service\File\CsvReader as CsvReader;
use Psr\Log\LoggerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Indexer\Model\IndexerFactory;

class ImportManager
{
    /**
     * @var ProductService
     */
    protected $productService;
    /**
     * @var CategoryService
     */
    protected $categoryService;
    /**
     * @var AttributeService
     */
    protected $attributeService;
    /**
     * @var DirectoryService
     */
    protected $directoryService;
    /**
     * @var CsvReader
     */
    protected $csvReader;
    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProductService $productService
     * @param CategoryService $categoryService
     * @param AttributeService $attributeService
     * @param DirectoryService $directoryService
     * @param CsvReader $csvReader
     * @param IndexerFactory $indexerFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        AttributeService $attributeService,
        DirectoryService $directoryService,
        CsvReader $csvReader,
        IndexerFactory $indexerFactory,
        LoggerInterface $logger
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->attributeService = $attributeService;
        $this->directoryService = $directoryService;
        $this->csvReader = $csvReader;
        $this->indexerFactory = $indexerFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info(__('BarberServices: Starting Import Process...'));
        $this->directoryService->createStructure();

        $this->runImport('attribute');
        $this->runImport('category');

        $this->reindex(['catalog_category_product']);

        $this->runImport('product');

        $this->reindex([
            'catalog_product_attribute',
            'catalog_product_price',
            'cataloginventory_stock',
            'catalog_product_category',
            'catalog_category_product',
            'catalogsearch_fulltext'
        ]);

        $this->logger->info(__('BarberServices: Import Process Completed Successfully!'));
    }

    /**
     * @param array $indexerIds
     * @return void
     */
    private function reindex(array $indexerIds): void
    {
        foreach ($indexerIds as $indexerId) {
            try {
                $indexer = $this->indexerFactory->create()->load($indexerId);
                $indexer->reindexAll();
            } catch (Exception $e) {
                $this->logger->error(__("BarberServices: Indexer %1 failed: %2", $indexerId, $e->getMessage()));
            }
        }
    }

    /**
     * @param string $type
     * @return void
     */
    private function runImport(string $type): void
    {
        $files = $this->directoryService->getFilesFromDir('source/' . $type);
        if (empty($files)) {
            $this->logger->info(__("BarberServices: No %1 files found.", $type));
            return;
        }

        if ($type === 'product') {
            $this->processProductFiles($files);
        } else {
            $this->processSimpleFiles($type, $files);
        }

        $this->directoryService->archiveEntityFiles($type);
    }

    /**
     * @param string $type
     * @param array $files
     * @return void
     */
    private function processSimpleFiles(string $type, array $files): void
    {
        $service = match ($type) {
            'attribute' => $this->attributeService,
            'category' => $this->categoryService,
            default => null
        };
        if (!$service) {
            return;
        }

        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                try {
                    $service->execute($row);
                } catch (Exception $e) {
                    $this->logger->error(__('BarberServices: %1 import failed. Error: %2', ucfirst($type), $e->getMessage()));
                }
            }
        }
    }

    /**
     * @param array $files
     * @return void
     */
    private function processProductFiles(array $files): void
    {
        $importData = $this->collectImportData($files);

        //import simple products
        foreach ($importData['simple'] as $row) {
            try {
                $this->productService->execute($row);
            } catch (Exception $e) {
                $this->logger->error(__('BarberServices: Simple SKU %1 failed. Error: %2', $row['sku'], $e->getMessage()));
            }
        }

        //import configurable products
        foreach ($importData['config'] as $sku => $row) {
            try {
                $this->productService->execute($row, $importData['links'][$sku]);
            } catch (Exception $e) {
                $this->logger->error(__('BarberServices: Config SKU %1 failed. Error: %2', $sku, $e->getMessage()));
            }
        }
    }

    private function collectImportData(array $files): array
    {
        $data = [
            'simple' => [],
            'config' => [],
            'links' => []
        ];

        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                if (empty($row['sku']) || empty($row['type'])) {
                    continue;
                }

                if ($row['type'] === Configurable::TYPE_CODE) {
                    $data['config'][$row['sku']] = $row;
                } else {
                    $data['simple'][] = $row;
                    if (!empty($row['parent_sku'])) {
                        $data['links'][$row['parent_sku']][] = $row['sku'];
                    }
                }
            }
        }
        return $data;
    }
}
