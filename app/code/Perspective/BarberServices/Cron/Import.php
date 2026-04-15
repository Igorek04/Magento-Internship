<?php
namespace Perspective\BarberServices\Cron;

use Perspective\BarberServices\Model\Queue\Publisher;
use Psr\Log\LoggerInterface;

class Import
{
    /**
     * @var Publisher
     */
    protected $publisher;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Publisher $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        Publisher $publisher,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->logger->info(__('BarberServices: Cron job has successfully dispatched the import task to the queue.'));
            $this->publisher->execute('cron');
        } catch (\Exception $e) {
            $this->logger->error(__('BarberServices: Cron failed to queue the import task. Error: ', $e->getMessage()));
        }
    }
}
