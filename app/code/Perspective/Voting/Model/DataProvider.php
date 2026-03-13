<?php
namespace Perspective\Voting\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;
use Perspective\Voting\Service\ConfigData;
use Magento\Backend\Model\Session as BackendSession;


class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;
    /**
     * @var ConfigData
     */
    protected $configDataService;
    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param OptionCollectionFactory $optionCollectionFactory
     * @param ConfigData $configDataService
     * @param BackendSession $backendSession
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        OptionCollectionFactory $optionCollectionFactory,
        ConfigData $configDataService,
        BackendSession $backendSession,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->configDataService = $configDataService;
        $this->backendSession = $backendSession;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * form data provider
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        // load voting data to edit voting
        foreach ($items as $item) {
            $data = $item->getData();
            $votingId = $item->getVotingId();

            // load voting options data
            $optionCollection = $this->optionCollectionFactory->create();
            $optionCollection->addFieldToFilter('voting_id', $votingId);
            $optionsData = [];
            foreach ($optionCollection as $option) {
                $optionsData[] = $option->getData();
            }
            $data['data']['options_container']['options_container'] = $optionsData;

            // is finished label for voting edit
            $isFinished = (int)($data['is_finished'] ?? 0);
            $data['is_finished_label'] = match ($isFinished) {
                1 => 'Yes',
                0 => 'No',
                default => 'Unknown State'
            };

            $data['config']['admin_allow_modify_votes'] = $this->configDataService->isAdminAllowedEditVotes();

            $this->loadedData[$votingId] = $data;
        }

        //load voting form data from session if previous save attempt failed
        if (empty($this->loadedData)) {
            $this->loadedData[null] = $this->backendSession->getNewVotingFormData();
        }

        return $this->loadedData;
    }
}
