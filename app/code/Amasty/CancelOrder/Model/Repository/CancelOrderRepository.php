<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model\Repository;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Amasty\CancelOrder\Api\CancelOrderRepositoryInterface;
use Amasty\CancelOrder\Model\CancelOrderFactory;
use Amasty\CancelOrder\Model\ResourceModel\CancelOrder as CancelOrderResource;
use Amasty\CancelOrder\Model\ResourceModel\CollectionFactory;
use Amasty\CancelOrder\Model\ResourceModel\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelOrderRepository implements CancelOrderRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CancelOrderFactory
     */
    private $cancelOrderFactory;

    /**
     * @var CancelOrderResource
     */
    private $cancelOrderResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $cancelOrders;

    /**
     * @var CollectionFactory
     */
    private $cancelOrderCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CancelOrderFactory $cancelOrderFactory,
        CancelOrderResource $cancelOrderResource,
        CollectionFactory $cancelOrderCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->cancelOrderFactory = $cancelOrderFactory;
        $this->cancelOrderResource = $cancelOrderResource;
        $this->cancelOrderCollectionFactory = $cancelOrderCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CancelOrderInterface $cancelOrder)
    {
        try {
            if ($cancelOrder->getId()) {
                $cancelOrder = $this->getById($cancelOrder->getId())->addData($cancelOrder->getData());
            }
            $this->cancelOrderResource->save($cancelOrder);
            unset($this->cancelOrders[$cancelOrder->getId()]);
        } catch (\Exception $e) {
            if ($cancelOrder->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save cancelOrder with ID %1. Error: %2',
                        [$cancelOrder->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new cancelOrder. Error: %1', $e->getMessage()));
        }

        return $cancelOrder;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->cancelOrders[$id])) {
            /** @var \Amasty\CancelOrder\Model\CancelOrder $cancelOrder */
            $cancelOrder = $this->cancelOrderFactory->create();
            $this->cancelOrderResource->load($cancelOrder, $id);
            if (!$cancelOrder->getId()) {
                throw new NoSuchEntityException(__('CancelOrder with specified ID "%1" not found.', $id));
            }
            $this->cancelOrders[$id] = $cancelOrder;
        }

        return $this->cancelOrders[$id];
    }

    /**
     * @return CancelOrderInterface
     */
    public function createNew()
    {
        return $this->cancelOrderFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(CancelOrderInterface $cancelOrder)
    {
        try {
            $this->cancelOrderResource->delete($cancelOrder);
            unset($this->cancelOrders[$cancelOrder->getId()]);
        } catch (\Exception $e) {
            if ($cancelOrder->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove cancelOrder with ID %1. Error: %2',
                        [$cancelOrder->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove cancelOrder. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $cancelOrderModel = $this->getById($id);
        $this->delete($cancelOrderModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\CancelOrder\Model\ResourceModel\CancelOrder\Collection $cancelOrderCollection */
        $cancelOrderCollection = $this->cancelOrderCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $cancelOrderCollection);
        }
        
        $searchResults->setTotalCount($cancelOrderCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $cancelOrderCollection);
        }
        
        $cancelOrderCollection->setCurPage($searchCriteria->getCurrentPage());
        $cancelOrderCollection->setPageSize($searchCriteria->getPageSize());
        
        $cancelOrders = [];
        /** @var CancelOrderInterface $cancelOrder */
        foreach ($cancelOrderCollection->getItems() as $cancelOrder) {
            $cancelOrders[] = $this->getById($cancelOrder->getId());
        }
        
        $searchResults->setItems($cancelOrders);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $cancelOrderCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $cancelOrderCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $cancelOrderCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $cancelOrderCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $cancelOrderCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $cancelOrderCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
