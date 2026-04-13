<?php

namespace Perspective\BarberServices\Service;

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
        $this->logger->info('BarberServices: Starting Import Process...');

        $this->directoryService->createStructure();

        $this->importAttributes();
        $this->importCategories();

        $this->reindex(['catalog_category_product']);

        $this->importProducts();

        $this->reindex([
            'catalog_product_attribute',
            'catalog_product_price',
            'cataloginventory_stock',
            'catalog_product_category',
            'catalog_category_product',
            'catalogsearch_fulltext'
        ]);

        $this->logger->info('BarberServices: Import Process Completed Successfully!');
    }

    private function reindex(array $indexerIds): void
    {
        foreach ($indexerIds as $indexerId) {
            try {
                $this->logger->info(sprintf('BarberServices: Running indexer "%s"...', $indexerId));
                $indexer = $this->indexerFactory->create()->load($indexerId);
                $indexer->reindexAll();
            } catch (\Exception $e) {
                $this->logger->error(sprintf('BarberServices: Failed to reindex "%s". Error: %s', $indexerId, $e->getMessage()));
            }
        }
    }

    private function importAttributes(): void
    {
        $files = $this->directoryService->getFilesFromDir('source/attribute');

        if (empty($files)) {
            $this->logger->info('BarberServices ImportManager: No attribute files found.');
            return;
        }

        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                try {
                    $this->attributeService->execute($row);
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(
                        'BarberServices ImportManager: Attribute import failed for row [%s]. Error: %s',
                        implode(', ', $row),
                        $e->getMessage()
                    ));
                }
            }
        }
        $this->directoryService->archiveEntityFiles('attribute');
    }

    private function importCategories(): void
    {
        $files = $this->directoryService->getFilesFromDir('source/category');

        if (empty($files)) {
            $this->logger->info('BarberServices ImportManager: No category files found.');
            return;
        }

        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                try {
                    $this->categoryService->execute($row);
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(
                        'BarberServices ImportManager: Category import failed for row [%s]. Error: %s',
                        implode(', ', $row),
                        $e->getMessage()
                    ));
                }
            }
        }
        $this->directoryService->archiveEntityFiles('category');
    }

    private function importProducts(): void
    {
        // check import files
        $files = $this->directoryService->getFilesFromDir('source/product');
        if (empty($files)) {
            $this->logger->info('BarberServices ImportManager: No product files found.');
            return;
        }

        $simpleProductsData = [];
        $configurableProductsData = [];
        $linkMap = [];

        $this->logger->info('BarberServices ImportManager: Reading product data...');
        foreach ($files as $file) {
            foreach ($this->csvReader->readFile($file) as $row) {
                if (empty($row['sku']) || empty($row['type'])) {
                    $this->logger->warning('BarberServices: Skipping row due to missing sku or type.', $row);
                    continue;
                }

                if ($row['type'] === Configurable::TYPE_CODE) {
                    $configurableProductsData[$row['sku']] = $row;
                } else {
                    $simpleProductsData[] = $row;
                    if (!empty($row['parent_sku'])) {
                        $linkMap[$row['parent_sku']][] = $row['sku'];
                    }
                }
            }
        }

        $this->logger->info(sprintf('BarberServices: Found %d simple and %d configurable products.', count($simpleProductsData), count($configurableProductsData)));

        $this->logger->info('BarberServices ImportManager: Importing simple products...');
        foreach ($simpleProductsData as $row) {
            try {
                $this->productService->execute($row);
            } catch (\Exception $e) {
                $this->logProductError($e, $row['sku']);
            }
        }

        $this->logger->info('BarberServices ImportManager: Importing configurable products...');
        foreach ($configurableProductsData as $sku => $row) {
            try {
                $childSkus = $linkMap[$sku] ?? [];
                $this->productService->execute($row, $childSkus);
            } catch (\Exception $e) {
                $this->logProductError($e, $sku);
            }
        }
        $this->directoryService->archiveEntityFiles('product');
    }

    private function logProductError(\Exception $e, string $sku): void
    {
        $this->logger->error(sprintf(
            'BarberServices: Product import failed for SKU [%s]. Error: %s. Previous: %s.',
            $sku,
            $e->getMessage(),
            $e->getPrevious() ? $e->getPrevious()->getMessage() : 'none'
        ));
    }
}
