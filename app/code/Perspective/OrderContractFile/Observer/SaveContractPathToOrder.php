<?php
namespace Perspective\OrderContractFile\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Session as AdminSession;

class SaveContractPathToOrder implements ObserverInterface
{
    /**
     * @var AdminSession
     */
    protected $adminSession;

    /**
     * @param AdminSession $adminSession
     */
    public function __construct(
        AdminSession $adminSession
    ) {
        $this->adminSession = $adminSession;
    }

    /**
     * Save contract file path to order from admin session
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $filePath = $this->adminSession->getData('contract_file_path');

        if ($filePath) {
            $order->setData('contract_file', $filePath);

            $this->adminSession->unsetData('contract_file');
        }
        return $this;
    }
}
