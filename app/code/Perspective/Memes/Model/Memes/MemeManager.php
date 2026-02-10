<?php

namespace Perspective\Memes\Model\Memes;

use Perspective\Memes\Api\GiphyApi;
use Perspective\Memes\Model\Memes\MemeDataHandler;
use Perspective\Memes\Service\ConfigData;


class MemeManager
{
    protected $giphyApi;
    protected $memeDataHandler;
    protected $configDataService;

    public function __construct(
        GiphyApi $giphyApi,
        MemeDataHandler $memeDataHandler,
        ConfigData $configDataService
    ) {
        $this->giphyApi = $giphyApi;
        $this->memeDataHandler = $memeDataHandler;
        $this->configDataService = $configDataService;
    }

    public function getData($entityId, $entityType): array
    {
        if (!$this->configDataService->isModuleEnabled()) {
            return [];
        }

        if (!$this->memeDataHandler->hasMemes($entityId, $entityType)) {
            $memesUrlArray = $this->giphyApi->getImagesUrl($entityId);
            $this->memeDataHandler->saveMemes($entityId, $entityType, $memesUrlArray);
        }
        return $this->memeDataHandler->getMemes($entityId, $entityType);
    }

    public function updateSelected(int $entityId, string $entityType, string $selected): void
    {
        $memesData = $this->memeDataHandler->getMemes($entityId, $entityType);
        $this->memeDataHandler->saveMemes($entityId, $entityType, $memesData['items'], $selected);
    }
}
