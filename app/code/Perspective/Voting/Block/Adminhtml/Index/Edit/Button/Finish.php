<?php
namespace Perspective\Voting\Block\Adminhtml\Index\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Perspective\Voting\Model\Source\ManagementType;
use Perspective\Voting\Model\ResourceModel\Voting;
use Perspective\Voting\Model\VotingFactory;
use Magento\Backend\Block\Widget\Context;
use Perspective\Voting\Service\VotingState;

class Finish extends Generic implements ButtonProviderInterface
{
    protected $votingStateService;



    public function __construct(
        Context $context,
        VotingState $votingStateService,

    ) {
        $this->votingStateService = $votingStateService;
        parent::__construct($context);
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $votingId = $this->context->getRequest()->getParam('voting_id');
        if (!$votingId) {
            return [];
        }

        $state = $this->votingStateService->getStateByVotingId($votingId);
        return match ($state) {
            VotingState::STATE_FINISHED => [
                'label' => __('Finished'),
                'class' => 'save primary',
                'on_click' => '',
                'sort_order' => 41,
                'disabled' => true
            ],
            VotingState::STATE_AUTO => [
                'label' => __('Auto Finish'),
                'class' => 'save primary',
                'on_click' => '',
                'sort_order' => 41,
                'disabled' => true
            ],
            VotingState::STATE_MANUAL_ACTIVE,
            VotingState::STATE_MANUAL_INACTIVE => [
                'label' => __('Finish Voting'),
                'class' => 'save primary',
                'on_click' => 'confirmSetLocation(\'' .
                    __('Are you sure you want to finish this voting? All votes will be recalculated and the form will be locked.') .
                    '\', \'' . $this->getFinishUrl() . '\')',
                'sort_order' => 41,
                'disabled' => false
            ]
        };
    }

    /**
     * @return string
     */
    public function getFinishUrl(): string
    {
        $votingId = $this->context->getRequest()->getParam('voting_id');
        return $this->getUrl('*/*/finish', ['voting_id' => $votingId]);
    }
}
