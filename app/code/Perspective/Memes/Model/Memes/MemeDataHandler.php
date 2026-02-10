<?php

namespace Perspective\Memes\Model\Memes;

use Exception;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Psr\Log\LoggerInterface;

class MemeDataHandler
{
    protected $quoteRepository;
    protected $orderRepository;
    protected $logger;

    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

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

    public function hasMemes(int $entityId, string $entityType): bool
    {
        $memes = $this->getMemes($entityId, $entityType);
        return !empty($memes['items']);
    }

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
