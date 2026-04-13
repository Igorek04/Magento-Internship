<?php
namespace Perspective\BarberServices\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Perspective\BarberServices\Service\ImportManager;
use Perspective\BarberServices\Service\File\Directory as DirectoryService;
use Magento\Framework\Controller\ResultFactory;

class Import extends Action
{
    /**
     * @var ImportManager
     */
    protected $importManager;
    /**
     * @var DirectoryService
     */
    protected $directoryService;

    /**
     * @param Context $context
     * @param ImportManager $importManager
     * @param DirectoryService $directoryService
     */
    public function __construct(
        Context $context,
        ImportManager $importManager,
        DirectoryService $directoryService
    ) {
        parent::__construct($context);
        $this->importManager = $importManager;
        $this->directoryService = $directoryService;
    }

    /**
     * @return ResponseInterface|Redirect|(Redirect&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();
            $filesFound = 0;

            // move files from tmp to source directory (catalog, product, attribute)
            foreach ($data as $key => $value) {
                if (str_contains($key, '_file') && is_array($value)) {
                    $entityType = str_replace('_file', '', $key);

                    $filesFound += $this->directoryService->moveFromTmpToSource($entityType, $value);
                }
            }

            if ($filesFound > 0) {
                $this->importManager->execute();
                $this->messageManager->addSuccessMessage(__('Import process finished. Files: %1', $filesFound));
            } else {
                $this->messageManager->addWarningMessage(__('No files found to import.'));
            }

        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
