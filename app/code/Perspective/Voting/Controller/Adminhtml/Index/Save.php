<?php
namespace Perspective\Voting\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Model\Voting as VotingModel;
use Perspective\Voting\Model\ResourceModel\Voting as VotingResourceModel;
use Perspective\Voting\Model\VotingOptionFactory as OptionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption as OptionResourceModel;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;

class Save extends Action
{
    protected $votingModel;
    protected $votingResourceModel;
    protected $adminSession;
    protected $optionFactory;
    protected $optionResourceModel;
    protected $optionCollectionFactory;



    public function __construct(
        Action\Context $context,
        VotingModel $votingModel,
        VotingResourceModel $votingResourceModel,
        Session $adminSession,
        OptionCollectionFactory $optionCollectionFactory,
        OptionResourceModel $optionResourceModel,
        OptionFactory $optionFactory
    ) {
        parent::__construct($context);
        $this->votingModel = $votingModel;
        $this->votingResourceModel = $votingResourceModel;
        $this->adminSession = $adminSession;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->optionResourceModel = $optionResourceModel;
        $this->optionFactory = $optionFactory;
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $votingId = $this->getRequest()->getParam('voting_id');

            $model = $this->votingModel;

            if ($votingId) {
                $this->votingResourceModel->load($model, $votingId);
            }

            $model->setData($data);

            try {
                $this->votingResourceModel->save($model);
                $votingId = $model->getId();

                $optionsData = $data['data']['options_container']['options_container'];
                if (is_array($optionsData)) {
                    // Получаем ID всех опций, которые пришли из формы, чтобы знать, что НЕ удалять
                    $processedOptionIds = [];

                    foreach ($optionsData as $option) {
                        // Создаем экземпляр модели опции через Factory (не забудь добавить в конструктор)
                        $optionModel = $this->optionFactory->create();

                        if (!empty($option['option_id'])) {
                            // Если ID есть, загружаем существующую
                            $this->optionResourceModel->load($optionModel, $option['option_id']);
                            $processedOptionIds[] = $option['option_id'];
                        }

                        // Устанавливаем данные и привязываем к голосованию
                        $optionModel->setData($option);
                        $optionModel->setVotingId($votingId);

                        $this->optionResourceModel->save($optionModel);

                        // Если это была новая опция, запоминаем её новый ID
                        if (empty($option['option_id'])) {
                            $processedOptionIds[] = $optionModel->getId();
                        }
                    }

                    // 3. УДАЛЕНИЕ: Удаляем из базы те опции, которые не пришли в запросе
                    // (юзер нажал на корзинку в Dynamic Rows)
                    $optionCollection = $this->optionCollectionFactory->create();
                    $optionCollection->addFieldToFilter('voting_id', $votingId);

                    if (!empty($processedOptionIds)) {
                        $optionCollection->addFieldToFilter('option_id', ['nin' => $processedOptionIds]);
                    }

                    foreach ($optionCollection as $optionToDelete) {
                        $this->optionResourceModel->delete($optionToDelete);
                    }
                }

















                $this->messageManager->addSuccessMessage(__('The data has been saved.'));
                $this->adminSession->setFormData(false);
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
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['voting_id' => $this->getRequest()->getParam('voting_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
