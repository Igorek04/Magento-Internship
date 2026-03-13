<?php
namespace Perspective\Voting\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Perspective\Voting\Service\VotingState;

class CurrentStatus extends Column
{
    /**
     * @var VotingState
     */
    protected $votingState;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param VotingState $votingState
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        VotingState $votingState,
        array $components = [],
        array $data = []
    ) {
        $this->votingState = $votingState;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $votingId = $item['voting_id'];
                $state = $this->votingState->getStateByVotingId($votingId);
                $endDate = $item['end_date'];

                $item[$this->getData('name')] = match ($state) {
                    VotingState::STATE_FINISHED => sprintf(
                        '<strong>%s</strong>',
                        __('Finished')
                    ),
                    VotingState::STATE_MANUAL_ACTIVE => sprintf(
                        '<span style="color: green;">%s</span>',
                        __('Active (Manual)')
                    ),
                    VotingState::STATE_MANUAL_INACTIVE => sprintf(
                        '<span style="color: grey;">%s</span>',
                        __('Inactive (Manual)')
                    ),
                    VotingState::STATE_AUTO => sprintf(
                        '<span style="color: green;">%s</span>',
                        __('Active (Auto - %1)', $endDate)
                    ),
                    default => sprintf(
                        '<span style="color: red;">%s</span>',
                        __('Unknown')
                    )
                };
            }
        }
        return $dataSource;
    }
}
