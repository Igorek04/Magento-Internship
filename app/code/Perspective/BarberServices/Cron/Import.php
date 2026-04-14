<?php
namespace Perspective\BarberServices\Cron;

use Perspective\BarberServices\Service\ImportManager;
use Psr\Log\LoggerInterface;

class Import
{
    /**
     * @var ImportManager
     */
    protected $importManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ImportManager $importManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImportManager $importManager,
        LoggerInterface $logger
    ) {
        $this->importManager = $importManager;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->logger->info(__('BarberServices: Cron import started.'));
            $this->importManager->execute();
            $this->logger->info(__('BarberServices: Cron import finished.'));
        } catch (\Exception $e) {
            $this->logger->error(__('BarberServices: Cron import failed. Error: ', $e->getMessage()));
        }
    }
}
