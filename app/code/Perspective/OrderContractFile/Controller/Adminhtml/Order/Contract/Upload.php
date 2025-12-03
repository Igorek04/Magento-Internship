<?php declare(strict_types=1);
namespace Perspective\OrderContractFile\Controller\Adminhtml\Order\Contract;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Backend\Model\Session as AdminSession;

class Upload extends Action
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var AdminSession
     */
    protected $adminSession;

    /**
     * @param Action\Context $context
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param AdminSession $adminSession
     */
    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdminSession $adminSession
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->adminSession = $adminSession;
    }

    /**
     * Upload contract file from uiComponent to server
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'contract_file_upload']);
            //uploader configuration
            $uploader->setAllowedExtensions(['pdf', 'jpg', 'jpeg', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath('order_contracts/')); // /a/b/file.pdf

            $mediaUrl = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
            $contractFilePath = 'order_contracts' . $result['file']; //order_contracts/a/b/file.pdf
            
            //save relative file path to admin session
            $this->adminSession->setData('contract_file_path', $contractFilePath);
            
            //create json for fileUploader uiComponent 
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'filename' => $result['file'],
                'url' => $mediaUrl . $contractFilePath,
                'name' => $result['name'],
                'type' => $result['type'],
                'size' => $result['size']
            ]);
        } catch (\Exception $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'error' => true,
                'message' => __('Failed to upload contract: %1', $e->getMessage())
            ]);
        }
    }
}
