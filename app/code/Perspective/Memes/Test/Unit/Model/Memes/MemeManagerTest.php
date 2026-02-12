<?php
namespace Perspective\Memes\Test\Unit\Model\Memes;

use Perspective\Memes\Api\GiphyApi;
use Perspective\Memes\Model\Memes\MemeDataHandler;
use Perspective\Memes\Model\Memes\MemeManager;
use Perspective\Memes\Service\ConfigData;
use PHPUnit\Framework\TestCase;

class MemeManagerTest extends TestCase
{
    /**
     * Test MemeManager::getData method
     *
     * This test checks the following scenarios:
     *  - Module is disabled - returns empty array, no API or save calls.
     *  - Module enabled and entity has no memes - calls Giphy API and saves memes.
     *  - Module enabled and entity already has memes - returns existing data, no API/save calls.
     *
     * @dataProvider getDataProvider
     */
    public function testGetData(
        bool $isModuleEnabled,
        string $entityType,
        int $entityId,
        bool $hasMemes,
        array $expectedData,
        bool $expectApiCall,
        bool $expectSaveCall
    ): void {
        // Mock ConfigData service to simulate module enabled/disabled
        $configMock = $this->createMock(ConfigData::class);
        $configMock->method('isModuleEnabled')->willReturn($isModuleEnabled);

        // Mock MemeDataHandler to control hasMemes() and getMemes()
        $handlerMock = $this->createMock(MemeDataHandler::class);
        $handlerMock->method('hasMemes')->willReturn($hasMemes);
        $handlerMock->method('getMemes')->willReturn($expectedData);

        // api call test
        $apiMock = $this->createMock(GiphyApi::class);
        if ($expectApiCall) {
            $apiMock->expects($this->once())
                ->method('getImagesUrl')
                ->with($entityId)
                ->willReturn($expectedData);
        } else {
            $apiMock->expects($this->never())
                ->method('getImagesUrl');
        }
        // saveMemes call test
        if ($expectSaveCall) {
            $handlerMock->expects($this->once())
                ->method('saveMemes')
                ->with($entityId, $entityType, $expectedData);
        } else {
            $handlerMock->expects($this->never())
                ->method('saveMemes');
        }

        // object
        $manager = new MemeManager($apiMock, $handlerMock, $configMock);

        // test function call
        $result = $manager->getData($entityId, $entityType);

        // check result
        $this->assertSame($expectedData, $result);
    }

    /**
     * Data provider for testGetData
     *
     * @return array
     */
    public function getDataProvider(): array
    {
        return [
            'module disabled' => [
                'isModuleEnabled' => false,
                'entityType' => 'quote',
                'entityId' => 1,
                'hasMemes' => false,
                'expectedData' => [],
                'expectApiCall' => false,
                'expectSaveCall' => false,
            ],
            'entity has no memes' => [
                'isModuleEnabled' => true,
                'entityType' => 'order',
                'entityId' => 2,
                'hasMemes' => false,
                'expectedData' => ['url1', 'url2'],
                'expectApiCall' => true,
                'expectSaveCall' => true,
            ],
            'entity has memes' => [
                'isModuleEnabled' => true,
                'entityType' => 'quote',
                'entityId' => 3,
                'hasMemes' => true,
                'expectedData' => ['url3'],
                'expectApiCall' => false,
                'expectSaveCall' => false,
            ],
        ];
    }
}
