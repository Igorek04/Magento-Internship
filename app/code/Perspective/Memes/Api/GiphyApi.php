<?php
namespace Perspective\Memes\Api;

use Exception;
use Perspective\Memes\Service\ConfigData;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Perspective\Memes\Service\MemeSearchWord;
use Perspective\Memes\Exception\GiphyApiException;

class GiphyApi
{
    /**
     * @var ConfigData
     */
    protected $configDataService;
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var MemeSearchWord
     */
    protected $memeSearchWordService;

    /**
     * @param ConfigData $configDataService
     * @param Curl $curl
     * @param LoggerInterface $logger
     * @param MemeSearchWord $memeSearchWordService
     */
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

    /**
     * Send request to Giphy API
     *
     * @param $entityId
     * @return array[]
     */
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

            // check correct response status
            if (!isset($response['meta']['status']) || $response['meta']['status'] != 200) {
                throw new GiphyApiException(__('Giphy API returned invalid status: %1', $response['meta']['status'] ?? 'null'));
            }
            // check is set response data and empty data
            if (empty($response['data'])) {
                throw new GiphyApiException(__('Giphy API returned empty or invalid data'));
            }

        } catch (Exception $e) {
            $this->logger->error(__('Giphy API request failed. %1', $e->getMessage()));
            $response = ['data' => []];
        }
        return $response;
    }

    /**
     * Get images url from response array
     *
     * @param $entityId
     * @return array
     */
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
