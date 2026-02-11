<?php
namespace Perspective\Memes\Model\Memes;

use Perspective\Memes\Api\GiphyApi;
use Perspective\Memes\Model\Memes\MemeDataHandler;
use Perspective\Memes\Service\ConfigData;

class MemeManager
{
    /**
     * @var GiphyApi
     */
    protected $giphyApi;
    /**
     * @var MemeDataHandler
     */
    protected $memeDataHandler;
    /**
     * @var ConfigData
     */
    protected $configDataService;

    /**
     * @param GiphyApi $giphyApi
     * @param MemeDataHandler $memeDataHandler
     * @param ConfigData $configDataService
     */
    public function __construct(
        GiphyApi $giphyApi,
        MemeDataHandler $memeDataHandler,
        ConfigData $configDataService
    ) {
        $this->giphyApi = $giphyApi;
        $this->memeDataHandler = $memeDataHandler;
        $this->configDataService = $configDataService;
    }

    /**
     * Get memes data for given entity (quote \ order)
     * If entity not have memes data - send request on Giphy for get memes data
     *
     * @param $entityId
     * @param $entityType
     * @return array
     */
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

    /**
     * Update selected meme for entity
     *
     * @param int $entityId
     * @param string $entityType
     * @param string $selected
     * @return void
     */
    public function updateSelected(int $entityId, string $entityType, string $selected): void
    {
        $memesData = $this->memeDataHandler->getMemes($entityId, $entityType);
        $this->memeDataHandler->saveMemes($entityId, $entityType, $memesData['items'], $selected);
    }
}
