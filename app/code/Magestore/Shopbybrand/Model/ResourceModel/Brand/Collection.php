<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\ResourceModel\Brand;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
//    protected $_storeManager;
    /**
     * {@inheritdoc}
     */
    protected $_storeId = null;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory
     */
//    protected $_brandValueResource;

    /**
     * @var array
     */
    protected $_storeField = ['brand_name', 'is_featured', 'title', 'meta_keywords', 'meta_description', 'short_description', 'description', 'is_active',];

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory
     */
    protected $_brandValueResource;
    protected $_brandProductsCollection;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory $brandProductsCollectionFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory $brandValueFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
        $this->_brandValueResource = $brandValueFactory;
        $this->_brandProductsCollection = $brandProductsCollectionFactory;
    }

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\Brand', 'Magestore\Shopbybrand\Model\ResourceModel\Brand');
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return $this
     */
//    public function setStoreId($storeId)
//    {
//        if ($storeId instanceof \Magento\Store\Api\Data\StoreInterface) {
//            $storeId = $storeId->getId();
//        }
//        $this->_storeId = (int)$storeId;
//        return $this;
//    }

    /**
     * Return current store id
     *
     * @return int
     */
//    public function getStoreId()
//    {
//        if ($this->_storeId === null) {
//            $this->setStoreId($this->_storeManager->getStore()->getId());
//        }
//        return $this->_storeId;
//    }

//    public function _afterLoad()
//    {
//        parent::_afterLoad();
//        if ($storeId = $this->getStoreId()) {
//            $storeField = (isset($array) && count($array)) ? $array : $this->_storeField;
//            foreach ($storeField as $value) {
//                $brandValue = $this->_brandValueResource->create()
//                    ->addFieldToFilter('store_id', $storeId)
//                    ->addFieldToFilter('code', $value)
//                    ->getSelect()
//                    ->assemble();
//
//                $this->getSelect()
//                    ->joinLeft(
//                        [
//                            'brand_value_' . $value => new \Zend_Db_Expr("($brandValue)"),
//                        ],
//                        'main_table.id = brand_value_' . $value . '.brand_id',
//                        [
//                            $value => 'IF(brand_value_' . $value . '.value IS NULL,main_table.' . $value . ',brand_value_' . $value . '.value)',
//                            'category_ids' => '10'
//                        ]
//                    );
//            }
//        }
//        return $this;
//    }




    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _resetCloneSelect(\Magento\Framework\DB\Select $select, $cols = null)
    {
        $cloneSelect = clone $select;
        $cloneSelect->reset(\Zend_Db_Select::ORDER);
        $cloneSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $cloneSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $cloneSelect->reset(\Zend_Db_Select::COLUMNS);

        if ($cols) {
            $cloneSelect->columns($cols);
        }

        $cloneSelect->resetJoinLeft();

        return $cloneSelect;
    }

    /**
     * @return array
     */
    public function getAllCategories()
    {
        $collection = $this->_brandProductsCollection->create();
        $idsSelect = $this->_resetCloneSelect($collection->getSelect(), 'main_table.category_ids');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * @param $table
     *
     * @return array
     */
    public function getCategoryIdsFromProducts($table)
    {
        $idsSelect = $this->_resetCloneSelect($this->getSelect(), $table . '.category_id');

        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function getAllField($name)
    {
        $idsSelect = $this->_resetCloneSelect($this->getSelect(), 'main_table.' . $name);

        return $this->getConnection()->fetchCol($idsSelect);
    }
}
