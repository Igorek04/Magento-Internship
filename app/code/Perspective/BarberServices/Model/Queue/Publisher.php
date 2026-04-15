<?php
namespace Perspective\BarberServices\Model\Queue;

use Magento\Framework\MessageQueue\PublisherInterface;

class Publisher
{
    const TOPIC_NAME = 'barber.services.import';

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Publish import message to queue
     */
    public function execute(string $message = 'run'): void
    {
        $this->publisher->publish(self::TOPIC_NAME, $message);
    }
}
