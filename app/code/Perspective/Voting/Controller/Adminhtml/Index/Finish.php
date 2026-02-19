<?php
namespace Perspective\Voting\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Perspective\Voting\Model\VotingFactory;
use Perspective\Voting\Model\ResourceModel\Voting as VotingResource;
use Perspective\Voting\Model\VotingManager;

class Finish extends Action
{
    protected $votingFactory;
    protected $votingResource;
    protected $votingManager;

    public function __construct(
        Context $context,
        VotingFactory $votingFactory,
        VotingResource $votingResource,
        VotingManager $votingManager,
    ) {
        $this->votingFactory = $votingFactory;
        $this->votingResource = $votingResource;
        $this->votingManager = $votingManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $votingId = $this->getRequest()->getParam('voting_id');

        $this->votingManager->finishVoting($votingId);

        $this->messageManager->addSuccessMessage(__('Voting finished.'));

        return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['voting_id' => $votingId]);
    }
}
