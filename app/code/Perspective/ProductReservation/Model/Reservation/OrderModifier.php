<?php
namespace Perspective\ProductReservation\Model\Reservation;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\OrderRepository;
use DateTime;

class OrderModifier {
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param TimezoneInterface $timezone
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        TimezoneInterface $timezone,
        OrderRepository $orderRepository,
    ) {
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyReservation($order) {
        //expiration date for comment
        $createdAt = $order->getCreatedAt();
        $expiredAt = $this->timezone->date(new DateTime($createdAt))->modify('+1 day')->format('Y-m-d H:i');

        //order reservation attribute, status, state, comment
        $order->setData('is_reservation', 1)
            ->setState('reservation')
            ->setStatus('reservation')
            ->addCommentToStatusHistory('Reserved until ' . $expiredAt);

        $this->orderRepository->save($order);

        $incrementId = $order->getIncrementId();
        return [
            'incrementId' => $incrementId,
            'expiredAt' => $expiredAt
        ];
    }
}
