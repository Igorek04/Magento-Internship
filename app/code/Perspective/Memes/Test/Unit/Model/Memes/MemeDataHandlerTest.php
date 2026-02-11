<?php
namespace Perspective\Memes\Test\Unit\Model\Memes;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Perspective\Memes\Model\Memes\MemeDataHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

class MemeDataHandlerTest extends TestCase
{
    /**
     * Test MemeDataHandler::getMemes()
     *
     * @dataProvider getMemesProvider
     *
     * @param array|null $entityData Data to simulate entity (id, type, order_memes) or null for "not found"
     * @param array $expectedReturn Valid return value from getMemes()
     */
    public function testGetMemes(?array $entityData, array $expectedReturn): void
    {
        // Create mocks for repositories and logger
        $quoteRepo = $this->createMock(CartRepositoryInterface::class);
        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // create handler object
        $handler = new MemeDataHandler(
            $quoteRepo,
            $orderRepo,
            $logger
        );

        if ($entityData !== null) {
            // Mock getData('order_memes')
            $entityMock = $this->getMockBuilder(stdClass::class)->addMethods(['getData'])->getMock();
            // Return the 'order_memes' value from provider when getData is called
            $entityMock->method('getData')->with('order_memes')->willReturn($entityData['order_memes']);

            // Mock repository get() method depending on entity type (MemeDataHandler::getEntity() simulation)
            match ($entityData['type']) {
                'quote' => $quoteRepo->method('get')->with($entityData['id'])->willReturn($entityMock),
                'order' => $orderRepo->method('get')->with($entityData['id'])->willReturn($entityMock),
            };

            // Use entity info from provider
            $entityId = $entityData['id'];
            $entityType = $entityData['type'];
        } else {
            // If entityData is null, simulate entity not found
            $quoteRepo->method('get')->willReturn(null);
            $orderRepo->method('get')->willReturn(null);

            $entityId = 1;
            $entityType = 'quote';
        }

        // summon tested function
        $result = $handler->getMemes($entityId, $entityType);

        // check result
        $this->assertSame($expectedReturn, $result);
    }

    /**
     * Test MemeDataHandler::hasMemes()
     *
     * Checks that:
     *  - returns false when 'items' in order_memes is empty
     *  - returns true when 'items' contains at least one URL
     */
    public function testHasMemes(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        // --- empty items case ---
        $emptyQuoteRepo = $this->createMock(CartRepositoryInterface::class);
        $emptyEntity = $this->getMockBuilder(stdClass::class)->addMethods(['getData'])->getMock();
        $emptyEntity->method('getData')->with('order_memes')->willReturn(json_encode(['items' => []]));
        $emptyQuoteRepo->method('get')->willReturn($emptyEntity);

        $handler = new MemeDataHandler($emptyQuoteRepo, $this->createMock(OrderRepositoryInterface::class), $logger);
        $this->assertFalse($handler->hasMemes(1, 'quote'));

        // --- non-empty items case ---
        $nonEmptyQuoteRepo = $this->createMock(CartRepositoryInterface::class);
        $nonEmptyEntity = $this->getMockBuilder(stdClass::class)->addMethods(['getData'])->getMock();
        $nonEmptyEntity->method('getData')->with('order_memes')->willReturn(json_encode(['items' => ['url1']]));
        $nonEmptyQuoteRepo->method('get')->willReturn($nonEmptyEntity);

        $handler = new MemeDataHandler($nonEmptyQuoteRepo, $this->createMock(OrderRepositoryInterface::class), $logger);
        $this->assertTrue($handler->hasMemes(2, 'quote'));
    }

    /**
     * Data provider for testGetMemes()
     *
     * Each scenario provides:
     *  - 'entityData': array with entity info (id, type, order_memes) or null if entity not found
     *  - 'expectedReturn': valid return value from getMemes()
     *
     * @return array[]
     */
    public function getMemesProvider(): array
    {
        return [
            'entity not found' => [
                'entityData' => null,
                'expectedReturn' => []
            ],

            'empty order_memes field' => [
                'entityData' => [
                    'id' => 1,
                    'type' => 'quote',
                    'order_memes' => null
                ],
                'expectedReturn' => []
            ],

            'valid json in order_memes' => [
                'entityData' => [
                    'id' => 2,
                    'type' => 'order',
                    'order_memes' => json_encode([
                        'selected' => null,
                        'items' => ['url1', 'url2']
                    ])
                ],
                'expectedReturn' => [
                    'selected' => null,
                    'items' => ['url1', 'url2']
                ]
            ]
        ];
    }
}
