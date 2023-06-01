<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Store\Model\Store;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Product extends Extended //implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_brandProductsData = [];

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
//    protected $_linkFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_brandHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory
     */
    protected $_brandProductsFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_brandCollectionFactory;
    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;
    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory
     */
    protected $_brandProductsCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\Product\LinkFactory $linkFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
//        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magestore\Shopbybrand\Model\BrandFactory $_brandFactory,
        \Magestore\Shopbybrand\Helper\Brand $brandHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory $brandProductsCollectionFactory,
        array $data = []
    ) {
//        $this->_linkFactory = $linkFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_coreRegistry = $coreRegistry;
        $this->moduleManager = $moduleManager;
        $this->_brandFactory = $_brandFactory;
        $this->_brandHelper = $brandHelper;
        $this->_objectManager = $objectManager;
        $this->_brandCollectionFactory = $brandCollectionFactory;
        $this->_brandProductsCollectionFactory = $brandProductsCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brand_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getBrand() && $this->getBrand()->getId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
//        if ($this->isReadonly()) {
//            $this->setFilterVisibility(false);
//        }
    }

    /**
     * @return $this
     */
    public function getBrand()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->_brandFactory->create()->load($id);
    }

    /**
     * @return Grid
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Add filter
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $store = $this->_getStore();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        )->setStore(
            $store
        );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        // join position field to product collection in case of editing a brand
        if ($this->getRequest()->getParam('id')) {
            // add sorting by product position in a brand
            if ($this->getRequest()->getParam('sort') == 'position') {
                $collection->joinField('sort_position', 'ms_brand_products', 'position', 'product_id=entity_id');
                $collection->addOrder('sort_position', strtoupper($this->getRequest()->getParam('dir')));
            }
        }
        $types = array_keys($this->getProductTypeIds());
        if (count($types)) {
            $collection->addFieldToFilter('type_id', ['in' => $types]);
        }

        $collection->addAttributeToFilter('entity_id', ['nin' => $this->getAllBrandProductIds()]);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return array
     */
    public function getProductTypeIds()
    {
        $attributeCode = $this->_brandHelper->getAttributeCode();
        $attributeModel = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute')->loadByCode('catalog_product', $attributeCode);
        $applyTo = $attributeModel->getData('apply_to');
        $types = $this->_objectManager->create('Magento\Catalog\Model\Product\Type')->getOptionArray();
        if (is_null($applyTo)) {
            return $types;
        }
        $productTypes = explode(',', $applyTo);
        $newTypes = [];
        foreach ($productTypes as $type) {
            if (key_exists($type, $types)) {
                $newTypes[$type] = $types[$type];
            }
        }
        return $newTypes;
    }

    /**
     * Checks when this block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getBrand() && $this->getBrand()->getRelatedReadonly();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn(
                'in_products',
                [
                    'type' => 'checkbox',
                    'name' => 'in_products',
                    'values' => $this->_getSelectedProducts(),
                    'align' => 'center',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select'
                ]
            );
        }

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_type->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'visibility_status',
            [
                'header'   => __('visibility_status'),
                'type'     => 'visibility_state',
                'index'    => 'visibility',
                'editable' => true,
                'filter'   => false,
                'header_css_class' => 'no-display',
                'column_css_class' => 'no-display',
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'is_featured',
            [
                'header' => __('Featured'),
                'type' => 'checkbox',
                'field_name' => 'featuredproducts[]',
                'values' => $this->_getFeaturedProducts(),
                'index' => 'entity_id',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => true,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'editable' => true,
                'validate_class' => 'validate-number',
                'index' => 'position',
//                'editable' => !$this->getBrand()->getRelatedReadonly(),
//                'edit_only' => !$this->getBrand()->getId(),
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getFeaturedProducts()
    {
        $brand = $this->getBrand();
        $productIds = explode(',', $brand->getProductIdsByBrandId($brand->getId()));
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\Collection $brandProductCollection */
        $brandProductCollection = $this->_brandProductsCollectionFactory->create()
            ->addFieldToFilter('product_id', ['in' => $productIds])
            ->addFieldToFilter('is_featured', 1);
        $featuredProductIds = [];
        foreach ($brandProductCollection as $item) {
            array_push($featuredProductIds, $item->getProductId());
        }
        return $featuredProductIds;
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = [];
        $brands = $this->getRequest()->getParam('id');
        if (!is_array($brands)) {
            $products = array_keys($this->getSelectedBrandProducts());
        }
        return $products;
    }

    /**
     * @return array
     */
    public function getSelectedBrandProducts()
    {
        if(empty($this->_brandProductsData)) {
            $products = [];
            $PositionsArray = $this->getPositionsArray();
            $brand = $this->getBrand();

            $productIds = $brand->getProductIdsByBrandId($brand->getId());
            $productIds = explode(',', $productIds);
            if (!$productIds) {
                $productIds = [];
            }
            $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect('id');
            $types = array_keys($this->getProductTypeIds());
            if (count($types)) {
                $collection->addFieldToFilter('type_id', ['in' => $types]);
            }
            $productIds = array_intersect($productIds, $collection->getAllIds());

            foreach ($productIds as $productId) {
                if (isset($PositionsArray[$productId])) {
                    $products[$productId] = [
                        'position' => $PositionsArray[$productId]['position'],
                        'visibility_status' => $PositionsArray[$productId]['visibility_status'],
                        'is_featured' => $PositionsArray[$productId]['is_featured']
                    ];
                } else {
                    $products[$productId] = [
                        'position' => 0,
                        'visibility_status' => 0,
                        'is_featured' => 0
                    ];
                }
            }
            $this->_brandProductsData = $products;
        }

        return $this->_brandProductsData;
    }

    /**
     * @return array
     */
    public function getPositionsArray()
    {
        $ArrayPositions = [];
        if($this->getBrand()) {
            $brandProducts = $this->_brandProductsCollectionFactory->create();
            $brandProducts->addFieldToFilter('brand_id', ['eq' => $this->getBrand()->getId()]);
            foreach ($brandProducts as $bp) {
                $ArrayPositions[$bp->getProductId()]['position'] = $bp->getPosition();
                $ArrayPositions[$bp->getProductId()]['visibility_status'] = $bp->getVisibilityStatus();
                $ArrayPositions[$bp->getProductId()]['is_featured'] = $bp->getIsFeatured();
            }
        }
        return $ArrayPositions;
    }



    /**
     * @return mixed
     */
    public function getAllBrandProductIds()
    {
        if($this->getBrand()){
            $brandCollection = $this->_brandProductsCollectionFactory->create();
            $brandCollection->addFieldToFilter('brand_id', ['neq' => $this->getBrand()->getId()]);
            $productIds = implode(',', $brandCollection->getColumnValues('product_id'));
            return explode(',', $productIds);
        }
        return [];
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/productsGrid',
            ['_current' => true]
        );
    }
}