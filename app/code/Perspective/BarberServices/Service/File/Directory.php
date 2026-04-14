<?php
namespace Perspective\BarberServices\Service\File;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
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


    /**
     * @var WriteInterface
     */
    protected $varDirectory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function createStructure(): void
    {
        try {
            foreach (self::MODES as $mode) {
                foreach (self::ENTITY_TYPES as $entity) {
                    $path = sprintf(self::PATH_TEMPLATE, $mode, $entity);

                    if (!$this->varDirectory->isExist($path)) {
                        $this->varDirectory->create($path);
                        $this->logger->info(__('BarberServices: Created var/%1', $path));
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error(__('BarberServices Directory Error: %1', $e->getMessage()));
        }
    }

    /**
     * @param string $subPath
     * @return array
     */
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

            $newFileName = sprintf('%s_%s', date('Ymd_His'), $fileName);
            $targetPath = sprintf('%s/%s', $archiveDir, $newFileName);

            try {
                if ($this->varDirectory->isExist($relativePath)) {
                    $this->varDirectory->renameFile($relativePath, $targetPath);
                    $this->logger->info(__('BarberServices: Archived %1 to %2', $fileName, $targetPath));
                }
            } catch (Exception $e) {
                $this->logger->error(__('BarberServices: Failed to archive %1. Error: %2', $fileName, $e->getMessage()));
            }
        }
    }

    /**
     * @param string $type
     * @param array $uploadedFiles
     * @return int
     * @throws FileSystemException
     */
    public function moveFromTmpToSource(string $type, array $uploadedFiles): int
    {
        $movedCount = 0;
        $tmpDir = sprintf(self::PATH_TEMPLATE, 'tmp', $type);
        $sourceDir = sprintf(self::PATH_TEMPLATE, 'source', $type);

        foreach ($uploadedFiles as $fileInfo) {
            $fileName = $fileInfo['file'];
            $sourceFile = sprintf('%s/%s', $tmpDir, $fileName);
            $destFile = sprintf('%s/%s', $sourceDir, $fileName);


            if ($this->varDirectory->isExist($sourceFile)) {
                $this->varDirectory->writeFile($destFile, $this->varDirectory->readFile($sourceFile));
                $this->varDirectory->delete($sourceFile);
                $movedCount++;
            }
        }
        $this->varDirectory->delete($tmpDir);

        return $movedCount;
    }

    /**
     * @param string $relativePath
     * @return void
     * @throws FileSystemException
     */
    public function clearDirectory(string $relativePath): void
    {
        if ($this->varDirectory->isExist($relativePath)) {
            $this->varDirectory->delete($relativePath);
        }
    }
}
