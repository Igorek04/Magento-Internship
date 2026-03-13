<?php
namespace Perspective\Voting\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Exception\VotingException;

class Save extends Action
{
    /**
     * @var Session
     */
    protected $adminSession;
    /**
     * @var VotingManager
     */
    protected $votingManager;
    /**
     * @var VotingOptionManager
     */
    protected $votingOptionManager;

    /**
     * @param Action\Context $context
     * @param Session $adminSession
     * @param VotingManager $votingManager
     * @param VotingOptionManager $votingOptionManager
     */
    public function __construct(
        Action\Context $context,
        Session $adminSession,
        VotingManager $votingManager,
        VotingOptionManager $votingOptionManager
    ) {
        parent::__construct($context);
        $this->adminSession = $adminSession;
        $this->votingManager = $votingManager;
        $this->votingOptionManager = $votingOptionManager;
    }

    /**
     * Trigger voting save process and redirect based on user action
     *
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
            } catch (VotingException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                //save form data to session to prevent data loss on error
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
