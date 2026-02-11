<?php

namespace Perspective\Memes\Test\Unit\Api;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Perspective\Memes\Service\ConfigData;
use Perspective\Memes\Service\MemeSearchWord;
use PHPUnit\Framework\TestCase;
use Perspective\Memes\Api\GiphyApi;
use Psr\Log\LoggerInterface;

class GiphyApiTest extends TestCase
{
    /**
     * Test GiphyApi::getImagesUrl() for extract only valid urls from api response.
     *
     * @return void
     */
    public function testGetImagesUrlReturnsUrlsArray(): void
    {
        $curlMock = $this->createMock(Curl::class);
        // test returned data
        $curlMock->method('getBody')->willReturn(json_encode([
            'meta' => ['status' => 200],
            'data' => [
                ['images' => ['fixed_height' => ['url' => 'url1']]], // standard
                [], // empty item
                ['images' => []], // without fixed_height
                ['images' => ['fixed_height' => []]], // without url
                ['not_images' => ['fixed_height' => ['url' => 'url2']]] // another field images name
            ]
        ]));

        // create object
        $api = new GiphyApi(
            $this->createMock(ConfigData::class),
            $curlMock,
            $this->createMock(LoggerInterface::class),
            $this->createMock(MemeSearchWord::class)
        );

        // summon tested function
        $result = $api->getImagesUrl(123);

        // check result
        $this->assertEquals(['url1'], $result);
    }

    /**
     * Test GiphyApi::request() for different API scenarios.
     *
     * @dataProvider requestResponseProvider
     *
     * @param array|null $curlResponse API response or null to simulate curl exception
     * @param bool $shouldLog Whether logger->error should be called
     * @param array $expectedReturn Valid returned array from request()
     * @return void
     */
    public function testRequestReturnsResponse(?array $curlResponse, bool $shouldLog, array $expectedReturn): void
    {
        // Mocks
        $curlMock = $this->createMock(Curl::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $configMock = $this->createMock(ConfigData::class);
        $searchWordMock = $this->createMock(MemeSearchWord::class);
        $searchWordMock->method('getSearchWordForQuote')->willReturn('Test');

        // Setup curl: throw exception or return JSON
        if ($curlResponse === null) {
            $curlMock->method('getBody')->willThrowException(new Exception('Curl error'));
        } else {
            $curlMock->method('getBody')->willReturn(json_encode($curlResponse));
        }

        // Setup logger expectation
        if ($shouldLog) {
            $loggerMock->expects($this->once())->method('error');
        } else {
            $loggerMock->expects($this->never())->method('error');
        }

        // create object and summon tested function
        $api = new GiphyApi($configMock, $curlMock, $loggerMock, $searchWordMock);
        $result = $api->request(123);

        // check result
        $this->assertSame($expectedReturn, $result);
    }

    /**
     * Data provider for testRequestReturnsResponse
     * Each scenario defines:
     *  - curlResponse: API response or null (simulate exception)
     *  - shouldLog: whether logger should be called
     *  - expectedReturn: valid output from request()
     *
     * @return array[]
     */
    public function requestResponseProvider(): array
    {
        return [
            'Curl exception' => [
                'curlResponse' => null,
                'shouldLog' => true,
                'expectedReturn' => ['data' => []],
            ],
            'No status field' => [
                'curlResponse' => ['meta' => []],
                'shouldLog' => true,
                'expectedReturn' => ['data' => []],
            ],
            'Status not 200' => [
                'curlResponse' => ['meta' => ['status' => 500], 'data' => []],
                'shouldLog' => true,
                'expectedReturn' => ['data' => []],
            ],
            'No data field' => [
                'curlResponse' => ['meta' => ['status' => 200]],
                'shouldLog' => true,
                'expectedReturn' => ['data' => []],
            ],
            'Valid response' => [
                'curlResponse' => ['meta' => ['status' => 200], 'data' => ['testdata']],
                'shouldLog' => false,
                'expectedReturn' => ['meta' => ['status' => 200], 'data' => ['testdata']],
            ],
        ];
    }
}
