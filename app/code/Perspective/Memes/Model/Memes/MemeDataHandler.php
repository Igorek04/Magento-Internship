<?php
namespace Perspective\Memes\Model\Memes;

use Exception;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Psr\Log\LoggerInterface;

class MemeDataHandler
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param QuoteRepository $quoteRepository
     * @param OrderRepository $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Get memes data from entity (quote \ order)
     * Decode stored JSON in 'order_memes' field
     *
     * @param int $entityId
     * @param string $entityType
     * @return array
     */
    public function getMemes(int $entityId, string $entityType): array
    {
        $object = $this->getEntity($entityType, $entityId);
        if (!$object) {
            return [];
        }

        $json = $object->getData('order_memes');
        // if JSON not empty -> decode, else []
        return $json ? json_decode($json, true) : [];
    }

    /**
     * Check if entity has memes data
     *
     * @param int $entityId
     * @param string $entityType
     * @return bool
     */
    public function hasMemes(int $entityId, string $entityType): bool
    {
        $memes = $this->getMemes($entityId, $entityType);
        return !empty($memes['items']);
    }

    /**
     * Save memes data to entity
     *
     * @param int $entityId
     * @param string $entityType
     * @param array $memesUrlArray
     * @param string|null $selected
     * @return void
     */
    public function saveMemes(int $entityId, string $entityType, array $memesUrlArray, ?string $selected = null): void
    {
        $object = $this->getEntity($entityType, $entityId);
        if (!$object) {
            return;
        }

        $data = [
            'selected' => $selected,
            'items' => $memesUrlArray
        ];

        $object->setData('order_memes', json_encode($data));
        $this->saveEntity($entityType, $object);
    }

    /**
     * Get entity object by type (quote \ order) and id
     *
     * @param string $entityType
     * @param int $entityId
     * @return CartInterface|OrderInterface|null
     */
    public function getEntity(string $entityType, int $entityId)
    {
        try {
            return match ($entityType) {
                'quote' => $this->quoteRepository->get($entityId),
                'order' => $this->orderRepository->get($entityId),
                default => null,
            };
        } catch (Exception $e) {
            $this->logger->error(__('MemeDataHandler: failed to get %1 with ID %2. %3',
                $entityType,
                $entityId,
                $e->getMessage()
            ));
            return null;
        }
    }

    /**
     * Save entity object (quote \ order) with updated meme data
     *
     * @param string $entityType
     * @param $object
     * @return void
     */
    public function saveEntity(string $entityType, $object): void
    {
        try {
            match ($entityType) {
                'quote' => $this->quoteRepository->save($object),
                'order' => $this->orderRepository->save($object),
                default => null,
            };
        } catch (Exception $e) {
            $id = $object->getId();
            $this->logger->error(__('MemeDataHandler: failed to save %1 with ID %2. %3',
                $entityType,
                $id,
                $e->getMessage()
            ));
        }
    }
}
