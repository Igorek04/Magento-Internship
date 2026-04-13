<?php

namespace Perspective\BarberServices\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Perspective\BarberServices\Service\ImportManager;

class Test implements ObserverInterface
{
    protected $importManager;

    public function __construct(
        ImportManager $importManager,
    ) {
        $this->importManager = $importManager;
    }

    public function execute(Observer $observer)
    {
        $this->importManager->execute();
    }
}
