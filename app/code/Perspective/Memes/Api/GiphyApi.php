<?php

namespace Perspective\Memes\Api;

use Perspective\Memes\Service\ConfigData;
use Magento\Framework\HTTP\Client\Curl;


class GiphyApi
{
    protected $configDataService;
    protected $curl;
    public function __construct(
        ConfigData $configDataService,
        Curl $curl,
    ) {
        $this->configDataService = $configDataService;
        $this->curl = $curl;
    }

    public function request()
    {
        $q = 'test';

        $limit = $this->configDataService->getGifCount();
        $apiKey = $this->configDataService->getGiphyApiKey();
        $apiUrl = $this->configDataService->getGiphyApiUrl();

        $url = sprintf('%s?api_key=%s&q=%s&limit=%s', $apiUrl, $apiKey, $q, $limit);

        $this->curl->get($url);
        $response = json_decode($this->curl->getBody(), true);

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
