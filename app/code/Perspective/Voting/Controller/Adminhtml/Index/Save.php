<?php
namespace Perspective\Voting\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Model\Source\ManagementType;
use Perspective\Voting\Model\Voting as VotingModel;
use Perspective\Voting\Model\ResourceModel\Voting as VotingResourceModel;
use Perspective\Voting\Model\VotingOptionFactory as OptionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption as OptionResourceModel;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;
use Perspective\Voting\Service\VotingValidation;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Model\VotingOptionManager;

class Save extends Action
{
    protected $votingModel;
    protected $votingResourceModel;
    protected $adminSession;
    protected $optionFactory;
    protected $optionResourceModel;
    protected $optionCollectionFactory;
    protected $votingValidationService;
    protected $votingManager;
    protected $votingOptionManager;



    public function __construct(
        Action\Context $context,
        VotingModel $votingModel,
        VotingResourceModel $votingResourceModel,
        Session $adminSession,
        OptionCollectionFactory $optionCollectionFactory,
        OptionResourceModel $optionResourceModel,
        OptionFactory $optionFactory,
        VotingValidation $votingValidationService,
        VotingManager $votingManager,
        VotingOptionManager $votingOptionManager
    ) {
        parent::__construct($context);
        $this->votingModel = $votingModel;
        $this->votingResourceModel = $votingResourceModel;
        $this->adminSession = $adminSession;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->optionResourceModel = $optionResourceModel;
        $this->optionFactory = $optionFactory;
        $this->votingValidationService = $votingValidationService;
        $this->votingManager = $votingManager;
        $this->votingOptionManager = $votingOptionManager;
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $votingId = $this->getRequest()->getParam('voting_id');
        if ($data) {
            try {
                $model = $this->votingManager->saveVotingData($data, $votingId);
                $votingId = $model->getId();

                $optionsData = $data['data']['options_container']['options_container'];
                $this->votingOptionManager->saveVotingOptions($votingId, $optionsData);


                $this->messageManager->addSuccessMessage(__('The data has been saved.'));
                $this->adminSession->setFormData(false);
                $this->_getSession()->unsetData('new_voting_form_data');
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return $resultRedirect->setPath('*/*/add');
                    } else {
                        return $resultRedirect->setPath('*/*/edit', ['voting_id' => $model->getId(), '_current' => true]);
                    }
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (!$votingId) {
                    $this->_getSession()->setData('new_voting_form_data', $data);
                }

            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['voting_id' => $this->getRequest()->getParam('voting_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
