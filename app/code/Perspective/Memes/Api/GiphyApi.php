<?php

namespace Perspective\Memes\Api;

use Exception;
use Perspective\Memes\Service\ConfigData;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Perspective\Memes\Service\MemeSearchWord;

class GiphyApi
{
    protected $configDataService;
    protected $curl;
    protected $logger;
    protected $memeSearchWordService;
    public function __construct(
        ConfigData $configDataService,
        Curl $curl,
        LoggerInterface $logger,
        MemeSearchWord $memeSearchWordService
    ) {
        $this->configDataService = $configDataService;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->memeSearchWordService = $memeSearchWordService;
    }

    public function request($entityId): array
    {
        try {
            $q = $this->memeSearchWordService->getSearchWordForQuote($entityId);

            $limit = $this->configDataService->getGifCount();
            $apiKey = $this->configDataService->getGiphyApiKey();
            $apiUrl = $this->configDataService->getGiphyApiUrl();

            $url = sprintf('%s?api_key=%s&q=%s&limit=%s', $apiUrl, $apiKey, $q, $limit);

            $this->curl->get($url);
            $response = json_decode($this->curl->getBody(), true);

            if (!isset($response['data'])) {
                $this->logger->error(__('Invalid response from Giphy API'));
                $response = ['data' => []];
            }
        } catch (Exception $e) {
            $this->logger->error(__('Giphy API request failed. %1', $e->getMessage()));
            $response = ['data' => []];
        }
        return $response;
    }

    public function getImagesUrl($entityId): array
    {
        $response = $this->request($entityId);

        //select 200px height images url
        $result = [];
        foreach ($response['data'] as $item) {
            if (isset($item['images']['fixed_height']['url'])) {
                $result[] = $item['images']['fixed_height']['url'];
            }
        }
        return $result;
    }
}
