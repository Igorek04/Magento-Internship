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
    protected $productService;
    protected $categoryService;
    protected $attributeService;
    protected $directoryService;
    protected $csvReader;
    protected $indexerFactory;
    protected $logger;

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

    private function processSimpleFiles(string $type, array $files): void
    {
        $service = match ($type) {
            'attribute' => $this->attributeService,
            'category' => $this->categoryService,
            default => null
        };
        if (!$service) return;

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

    private function processProductFiles(array $files): void
    {
        $simpleData = [];
        $configData = [];
        $linkMap = [];

        //collect data
        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                if (empty($row['sku']) || empty($row['type'])) continue;

                if ($row['type'] === Configurable::TYPE_CODE) {
                    $configData[$row['sku']] = $row;
                } else {
                    $simpleData[] = $row;
                    if (!empty($row['parent_sku'])) {
                        $linkMap[$row['parent_sku']][] = $row['sku'];
                    }
                }
            }
        }

        //import simple products
        foreach ($simpleData as $row) {
            try {
                $this->productService->execute($row);
            } catch (Exception $e) {
                $this->logger->error(__('BarberServices: Simple SKU %1 failed. Error: %2', $row['sku'], $e->getMessage()));
            }
        }

        //import configurable products
        foreach ($configData as $sku => $row) {
            try {
                $this->productService->execute($row, $linkMap[$sku]);
            } catch (Exception $e) {
                $this->logger->error(__('BarberServices: Config SKU %1 failed. Error: %2', $sku, $e->getMessage()));
            }
        }
    }
}
