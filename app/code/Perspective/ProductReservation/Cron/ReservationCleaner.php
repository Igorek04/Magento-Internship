<?php
namespace Perspective\ProductReservation\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ReservationCleaner
{
    protected $orderCollectionFactory;
    protected $orderRepository;
    protected $timezone;


    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        TimezoneInterface $timezone
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
    }


    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('is_reservation', 1);
        $collection->addFieldToFilter('state', 'new');
        $collection->addFieldToFilter('status', 'pending');

        foreach ($collection as $order) {
            $createdAt = $this->timezone->date(new \DateTime($order->getCreatedAt()));
            $expiredAt = (clone $createdAt)->modify('+24 hours');
            $currentDate = $this->timezone->date();
            if ($expiredAt < $currentDate) {
                $order->cancel();
                $order->addCommentToStatusHistory('Reservation cancelled due to expired date');
                $this->orderRepository->save($order);
            }
        }

        echo "cron test";
    }
}
