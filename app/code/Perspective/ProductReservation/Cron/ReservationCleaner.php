<?php
namespace Perspective\ProductReservation\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Perspective\ProductReservation\Helper\Email;

class ReservationCleaner
{
    protected $orderCollectionFactory;
    protected $orderRepository;
    protected $timezone;
    protected $logger;
    protected $emailSender;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        Email $emailSender
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->emailSender = $emailSender;
    }


    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('is_reservation', 1);
            $collection->addFieldToFilter('status', 'reservation');

            $countCanceled = 0;
            foreach ($collection as $order) {
                $createdAt = $this->timezone->date(new \DateTime($order->getCreatedAt()));
                $expiredAt = (clone $createdAt)->modify('+24 hours');
                $currentDate = $this->timezone->date();
                if ($expiredAt < $currentDate) {
                    $order->cancel();
                    $order->addCommentToStatusHistory('Reservation cancelled due to expired date');
                    $this->orderRepository->save($order);

                    //send expiration email to customer
                    $customerEmail = $order->getCustomerEmail();
                    $customerName = $order->getCustomerFirstName();
                    $order_id = $order->getRealOrderId();
                    $this->emailSender->send(
                        'reservation_expired_customer_email',
                        $customerEmail,
                        [
                            'name' => $customerName,
                            'order_id' => $order_id,
                        ]
                    );
                    $countCanceled++;
                }
                $this->logger->info('Canceled expired reservations: ' . $countCanceled);
            }
        } catch (\Exception $e){
            $this->logger->error($e->getMessage());
        }
    }
}
