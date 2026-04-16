<?php
namespace Perspective\CustomerQuestions\Model\ResourceModel\Answer\Grid;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document as Model;
use Perspective\CustomerQuestions\Model\ResourceModel\Answer\Collection as EntityCollection;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\RequestInterface;

class Collection extends EntityCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;
    protected $request;


    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param $mainTable
     * @param $eventPrefix
     * @param $eventObject
     * @param $resourceModel
     * @param $model
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        RequestInterface $request,
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
                               $mainTable,
                               $eventPrefix,
                               $eventObject,
                               $resourceModel,
                               $model = Model::class,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->request = $request;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations): static
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAllIds(int $limit = null, int $offset = null): array
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria(): SearchCriteriaInterface|null
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null): static
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount): static
    {
        return $this;
    }

    /**
     * @param array|null $items
     * @return $this
     */
    public function setItems(array $items = null): static
    {
        return $this;
    }

    // send question_id filter
    protected function _renderFiltersBefore()
    {
        $questionId = $this->request->getParam('question_id');

        if (!$questionId) {
            $filters = $this->request->getParam('filters');
            if (isset($filters['question_id'])) {
                $questionId = $filters['question_id'];
            } else {
                $questionId = null;
            }
        }


        if (!$questionId) {
            $referer = $this->request->getServer('HTTP_REFERER');
            if ($referer && str_contains($referer, 'customerquestions/question/edit')) {
                if (preg_match('/entity_id\/(\d+)/', $referer, $matches)) {
                    $questionId = $matches[1];
                }
            }
        }

        if ($questionId) {
            $this->addFieldToFilter('main_table.question_id', ['eq' => $questionId]);
        }

        parent::_renderFiltersBefore();
    }
}
