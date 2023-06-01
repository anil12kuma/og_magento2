<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model\ResourceModel;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

class Grid extends Collection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var array
     */
    private $mappedFields = [
        'created_at' => 'main_table.created_at',
        'status' => 'main_table.status'
    ];

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
    }

    /**
     * @return mixed
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param array|null $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $orderTable = $this->getResource()->getTable('sales_order');
        $this->getSelect()->joinLeft(
            ['order' => $orderTable],
            "order.entity_id = main_table.order_id",
            ['increment_id', 'customer_email']
        );
        $this->getSelect()->group('main_table.id');

        parent::_renderFiltersBefore();
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }
        return parent::addOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::setOrder($field, $direction);
    }
}
