<?php
namespace Perspective\OrderContractFile\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class UnattachedFileCleaner
{
    protected $orderCollectionFactory;
    protected $driverFile;
    protected $directoryList;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        DirectoryList $directoryList,
        File $driverFile
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
    }

    /**
     * Delete unattached contract files from media
     */
    public function execute()
    {
        $mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA) . '/order_contracts/';
        // skip if folder doesnt exist
        if (!$this->driverFile->isExists($mediaPath)) {
            return $this;
        }

        $orders = $this->orderCollectionFactory->create();
        $usedFiles = $orders->getColumnValues('contract_file');
        $allFiles = $this->driverFile->readDirectoryRecursively($mediaPath);

        $totalFiles = 0;
        $deletedFiles = 0;
        // check each file and delete if not attached to any order
        foreach ($allFiles as $file) {
            if ($this->driverFile->isFile($file)) {
                $totalFiles++;
                $relativePath = 'order_contracts/' . str_replace($mediaPath, '', $file);

                if (!in_array($relativePath, $usedFiles)) {
                    $this->driverFile->deleteFile($file);
                    $deletedFiles++;
                }
            }
        }
        // Log result
        echo "[" . date('Y-m-d H:i:s') . "] " . "Unattached contract files cleaned. ({$deletedFiles}/{$totalFiles})" . "\n";
        return $this;
    }
}