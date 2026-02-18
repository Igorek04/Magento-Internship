<?php
namespace Perspective\Voting\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;
use Perspective\Voting\Service\ConfigData;


class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    protected $optionCollectionFactory;
    protected $configDataService;


    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        OptionCollectionFactory $optionCollectionFactory,
        ConfigData $configDataService,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->configDataService = $configDataService;
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

        foreach ($items as $item) {
            $data = $item->getData();
            $votingId = $item->getVotingId();

            // 1. Отримуємо опції для цього конкретного голосування
            $optionCollection = $this->optionCollectionFactory->create();
            $optionCollection->addFieldToFilter('voting_id', $votingId);

            // 2. Додаємо дані опцій у масив під потрібним ключем
            // Метод getItems() + перебір, щоб отримати чистий масив даних
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

        // is finished label for add(new) voting
        if (empty($this->loadedData)) {
            $this->loadedData[null] = [
                'is_finished_label' => 'No',
                'options_container' => []
            ];
        }

        return $this->loadedData;
    }
}
