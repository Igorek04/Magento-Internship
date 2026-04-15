<?php
namespace Perspective\BarberServices\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\BarberServices\Service\File\Directory as DirectoryService;

class Upload extends Action
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var DirectoryService
     */
    protected $directoryService;

    /**
     * @param Context $context
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param DirectoryService $directoryService
     */
    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        DirectoryService $directoryService
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->directoryService = $directoryService;
    }

    /**
     * @return ResponseInterface|Json|(Json&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        try {
            //get request params
            $type = $this->getRequest()->getParam('type', 'misc'); //entity type(attribute, product, category)
            $files = $this->getRequest()->getFiles()->toArray();
            $fieldName = key($files);

            //prepare path and clear
            $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $relativePath = sprintf(DirectoryService::PATH_TEMPLATE, 'tmp', $type);
            $this->directoryService->clearDirectory($relativePath);

            //init and configure uploader
            $uploader = $this->uploaderFactory->create(['fileId' => $fieldName]);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            //upload new file
            $uploadResult = $uploader->save($varDirectory->getAbsolutePath($relativePath));

            //response
            $result = [
                'file' => $uploadResult['file'],
                'name' => $uploadResult['name'],
                'size' => $uploadResult['size'],
                'status' => 'success'
            ];
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
