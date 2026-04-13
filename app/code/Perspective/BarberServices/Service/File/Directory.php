<?php
namespace Perspective\BarberServices\Service\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

class Directory
{
    /**
     * var/import/barber-services/-/-
     */
    const PATH_TEMPLATE = 'import/barber-services/%s/%s';
    const MODES = ['source', 'archive', 'tmp'];
    const ENTITY_TYPES = ['attribute', 'category', 'product'];


    protected $varDirectory;

    protected $logger;

    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->logger = $logger;
    }

    public function createStructure(): void
    {
        try {
            foreach (self::MODES as $mode) {
                foreach (self::ENTITY_TYPES as $entity) {
                    $path = sprintf(self::PATH_TEMPLATE, $mode, $entity);

                    if (!$this->varDirectory->isExist($path)) {
                        $this->varDirectory->create($path);
                        $this->logger->info(sprintf('BarberServices: Created var/%s', $path));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('BarberServices Directory Error: %s', $e->getMessage()));
        }
    }

    public function getFilesFromDir(string $subPath): array
    {
        $parts = explode('/', $subPath);
        $relativePath = sprintf(self::PATH_TEMPLATE, $parts[0], $parts[1]);

        $foundFiles = $this->varDirectory->search('*.csv', $relativePath);

        $absolutePaths = [];
        foreach ($foundFiles as $file) {
            $absolutePaths[] = $this->varDirectory->getAbsolutePath($file);
        }

        return $absolutePaths;
    }

    /**
     * @param string $entityType (attribute|category|product)
     * @return void
     */
    public function archiveEntityFiles(string $entityType): void
    {
        $sourceDir = sprintf(self::PATH_TEMPLATE, 'source', $entityType);
        $archiveDir = sprintf(self::PATH_TEMPLATE, 'archive', $entityType);

        $files = $this->varDirectory->search('*.csv', $sourceDir);

        foreach ($files as $relativePath) {
            $fileName = basename($relativePath);

            $newFileName = date('Ymd_His') . '_' . $fileName;
            $targetPath = $archiveDir . '/' . $newFileName;

            try {
                if ($this->varDirectory->isExist($relativePath)) {
                    $this->varDirectory->renameFile($relativePath, $targetPath);
                    $this->logger->info(sprintf('BarberServices: Archived %s to %s', $fileName, $targetPath));
                }
            } catch (\Exception $e) {
                $this->logger->error(sprintf('BarberServices: Failed to archive %s. Error: %s', $fileName, $e->getMessage()));
            }
        }
    }

    public function moveFromTmpToSource(string $type, array $uploadedFiles): int
    {
        $movedCount = 0;
        $tmpDir = sprintf(self::PATH_TEMPLATE, 'tmp', $type);
        $sourceDir = sprintf(self::PATH_TEMPLATE, 'source', $type);

        foreach ($uploadedFiles as $fileInfo) {
            $fileName = $fileInfo['file'];
            $sourceFile = $tmpDir . '/' . $fileName;
            $destFile = $sourceDir . '/' . $fileName;

            if ($this->varDirectory->isExist($sourceFile)) {
                $this->varDirectory->writeFile($destFile, $this->varDirectory->readFile($sourceFile));
                $this->varDirectory->delete($sourceFile);
                $movedCount++;
            }
        }

        $this->varDirectory->delete($tmpDir);

        return $movedCount;
    }

    public function clearDirectory(string $relativePath): void
    {
        if ($this->varDirectory->isExist($relativePath)) {
            $this->varDirectory->delete($relativePath);
        }
    }
}
