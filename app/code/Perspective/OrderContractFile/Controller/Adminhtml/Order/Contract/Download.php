<?php
namespace Perspective\OrderContractFile\Controller\Adminhtml\Order\Contract;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends Action
{
    /**
     * @var OrderRepositoryInterface 
     */
    protected $orderRepository;

    /**
     * @var FileFactory 
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Action\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Action\Context $context,
        OrderRepositoryInterface $orderRepository,
        FileFactory $fileFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Download order contract file from server
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $relativeFilePath = $order->getData('contract_file');
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $absoluteFilePath = $mediaDirectory->getAbsolutePath($relativeFilePath);

        //check if file exists (maybe deleted manually)
        if (!$mediaDirectory->isFile($relativeFilePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('File does not exist.'));
        }
        
        // send file to browser for download
        return $this->fileFactory->create(
            basename($relativeFilePath),
            [
                'type'  => 'filename', 
                'value' => $relativeFilePath,
                'rm'    => false
            ],
            DirectoryList::MEDIA,
            mime_content_type($absoluteFilePath)
        );
    }
}
