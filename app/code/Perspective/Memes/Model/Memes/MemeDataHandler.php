<?php

namespace Perspective\Memes\Model\Memes;

use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;

class MemeDataHandler
{
    protected $quoteRepository;
    protected $orderRepository;

    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
    }

    public function getMemes(int $entityId, string $entityType): array //exc
    {
        $object = $this->getEntity($entityType, $entityId);
        if (!$object) {
            return []; // нужно ли и мб поменять на эксепшены?
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




    private function getEntity(string $entityType, int $entityId)
    {
        return match ($entityType) {
            'quote' => $this->quoteRepository->get($entityId),
            'order' => $this->orderRepository->get($entityId),
            default => null,
        };
    }

    private function saveEntity(string $entityType, $object): void
    {
        match ($entityType) {
            'quote' => $this->quoteRepository->save($object),
            'order' => $this->orderRepository->save($object),
            default => null,
        };
    }
}
