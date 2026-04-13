<?php

namespace Perspective\BarberServices\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $filesystem;
    protected $storeManager;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            //get entity type from url parameters (attribute, catalog, product)
            $type = $this->getRequest()->getParam('type', 'misc');
            $files = $this->getRequest()->getFiles()->toArray();
            $fieldName = key($files);

            $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $relativeDestinationPath = 'import/barber-services/tmp/' . $type;

            //clear tmp directory before upload new file
            if ($varDirectory->isExist($relativeDestinationPath)) {
                $oldFiles = $varDirectory->search('*', $relativeDestinationPath);
                foreach ($oldFiles as $oldFile) {
                    $varDirectory->delete($oldFile);
                }
            }

            //init and configure uploader
            $uploader = $this->uploaderFactory->create(['fileId' => $fieldName]);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            //upload new file
            $result = $uploader->save($varDirectory->getAbsolutePath($relativeDestinationPath));

            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
            $result['url'] = $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . $relativeDestinationPath . '/' . $result['file'];

        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }


}
