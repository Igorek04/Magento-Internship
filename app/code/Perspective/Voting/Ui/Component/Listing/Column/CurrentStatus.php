<?php
namespace Perspective\Voting\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Perspective\Voting\Service\VotingState;

class CurrentStatus extends Column
{
    protected $votingState;

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

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $votingId = $item['voting_id'];
                $state = $this->votingState->getStateByVotingId($votingId);
                $endDate = $item['end_date'];

                $item[$this->getData('name')] = match ($state) {
                    VotingState::STATE_FINISHED => '<strong>' . __('Finished') . '</strong>',
                    VotingState::STATE_MANUAL_ACTIVE => '<span style="color: green;">' . __('Active (Manual)') . '</span>',
                    VotingState::STATE_MANUAL_INACTIVE => '<span style="color: grey;">' . __('Inactive (Manual)') . '</span>',
                    VotingState::STATE_AUTO => '<span style="color: green;">' . __('Active (Auto - %1)', $endDate) . '</span>',
                    default => '<span style="color: red;">' . __('Unknown') . '</span>'
                };
            }
        }
        return $dataSource;
    }
}
