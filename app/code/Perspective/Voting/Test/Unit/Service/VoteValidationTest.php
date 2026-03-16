<?php
namespace Perspective\Voting\Test\Unit\Service;

use PHPUnit\Framework\TestCase;
use Perspective\Voting\Service\VotingValidation;
use Perspective\Voting\Model\Voting;
use Perspective\Voting\Exception\VotingException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Perspective\Voting\Model\Source\ManagementType;

class VoteValidationTest extends TestCase
{
    protected $model;

    protected function setUp(): void
    {
        $timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->model = new VotingValidation($timezoneMock);
    }

    /**
     * @dataProvider canVoteDataProvider
     */
    public function testCanVote($identity, $isFinished, $type, $status, $endDate, $expectedMsg)
    {
        $voting = $this->getMockBuilder(Voting::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsFinished', 'getManagementType', 'getStatus', 'getEndDate'])
            ->getMock();

        $voting->method('getIsFinished')->willReturn($isFinished);
        $voting->method('getManagementType')->willReturn($type);
        $voting->method('getStatus')->willReturn($status);
        $voting->method('getEndDate')->willReturn($endDate);

        if ($expectedMsg) {
            $this->expectException(VotingException::class);
            $this->expectExceptionMessage($expectedMsg);
        }

        $this->model->canVote($voting, $identity);

        if (!$expectedMsg) {
            $this->assertTrue(true);
        }
    }

    public function canVoteDataProvider(): array
    {
        return [
            'Fail: No identification' => [
                'identity' => ['customer_id' => null, 'guest_hash' => null],
                'isFinished' => false,
                'type' => ManagementType::TYPE_MANUAL,
                'status' => 1,
                'endDate' => '2030-01-01',
                'expectedMsg' => 'Identification failed.'
            ],
            'Fail: Voting already finished' => [
                'identity' => ['customer_id' => 1, 'guest_hash' => null],
                'isFinished' => true,
                'type' => ManagementType::TYPE_MANUAL,
                'status' => 1,
                'endDate' => '2030-01-01',
                'expectedMsg' => 'Voting already finished.'
            ],
            'Fail: Manual type but status is disabled' => [
                'identity' => ['customer_id' => 1, 'guest_hash' => null],
                'isFinished' => false,
                'type' => ManagementType::TYPE_MANUAL,
                'status' => 0,
                'endDate' => '2030-01-01',
                'expectedMsg' => 'Voting is temporarily disabled.'
            ],
            'Fail: Auto type but end date expired' => [
                'identity' => ['customer_id' => 1, 'guest_hash' => null],
                'isFinished' => false,
                'type' => ManagementType::TYPE_BY_DATE,
                'status' => 1,
                'endDate' => '2020-01-01',
                'expectedMsg' => 'The voting period has expired.'
            ],
            'Success: Valid manual vote' => [
                'identity' => ['customer_id' => 1, 'guest_hash' => null],
                'isFinished' => false,
                'type' => ManagementType::TYPE_MANUAL,
                'status' => 1,
                'endDate' => '2030-01-01',
                'expectedMsg' => null
            ]
        ];
    }
}
