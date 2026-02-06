<?php

namespace Perspective\Memes\Model\Memes;

use Perspective\Memes\Api\GiphyApi;
use Perspective\Memes\Model\Memes\MemeDataHandler;


class MemeManager
{
    protected $giphyApi;
    protected $memeDataHandler;

    public function __construct(
        GiphyApi $giphyApi,
        MemeDataHandler $memeDataHandler
    ) {
        $this->giphyApi = $giphyApi;
        $this->memeDataHandler = $memeDataHandler;
    }

    public function getData($entityId, $entityType)
    {
        if (!$this->memeDataHandler->hasMemes($entityId, $entityType)) {
            $memesUrlArray = $this->giphyApi->getImagesUrl();
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
