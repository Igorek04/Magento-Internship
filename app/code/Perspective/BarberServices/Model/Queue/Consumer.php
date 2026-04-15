<?php
namespace Perspective\BarberServices\Model\Queue;

use Perspective\BarberServices\Service\ImportManager;
use Psr\Log\LoggerInterface;

class Consumer
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
     * Process import from queue
     */
    public function process(string $message): void
    {
        try {
            $this->logger->info(__('Queue: Starting import. Message: %1', $message));
            $this->importManager->execute();
        } catch (\Exception $e) {
            $this->logger->error(__('Queue: Import error: %1', $e->getMessage()));
        }
    }
}
