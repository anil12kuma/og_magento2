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
use Magento\Framework\App\ResourceConnection;

/**
 * Class Tab GeneralTab
 */
class SoldItems extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\Brand
     */
    protected $_brand;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory
     */
    protected $_brandProductsFactory;

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_brandHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_saleItemsFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * SoldItems constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magestore\Shopbybrand\Model\BrandFactory $_brandFactory
     * @param \Magestore\Shopbybrand\Model\Brand $brand
     * @param \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory $brandProductsCollectionFactory
     * @param \Magestore\Shopbybrand\Helper\Brand $brandHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param ResourceConnection $resource
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $_saleItemsFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\Shopbybrand\Model\BrandFactory $_brandFactory,
        \Magestore\Shopbybrand\Model\Brand $brand,
        \Magestore\Shopbybrand\Model\ResourceModel\BrandProducts\CollectionFactory $brandProductsCollectionFactory,
        \Magestore\Shopbybrand\Helper\Brand $brandHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        ResourceConnection $resource,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $_saleItemsFactory,
        \Magento\Framework\Module\Manager $moduleManager,

        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_brandFactory = $_brandFactory;
        $this->_brand = $brand;
        $this->_status = $status;
        $this->_resource = $resource;
        $this->_visibility = $visibility;
        $this->_brandProductsFactory = $brandProductsCollectionFactory;
        $this->_setsFactory = $setsFactory;
        $this->moduleManager = $moduleManager;
        $this->_brandHelper = $brandHelper;
        $this->_saleItemsFactory = $_saleItemsFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_items_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $brand = $this->_brandFactory->create()->load($this->getRequest()->getParam('id'));
        $productIDs = explode(',', $brand->getProductIdsByBrandId($brand->getId()));

        $collection = $this->_saleItemsFactory->create()->addFieldToFilter('product_id', ['in' => $productIDs]);
        $sfog = $this->_resource->getTableName('sales_order_grid');
        $collection->getSelect()
            ->join(
                ['sfog' => $sfog],
                'main_table.order_id = sfog.entity_id AND sfog.status = "complete"',
                ['billing_name', 'shipping_name']
            );
        $store = $this->getRequest()->getParam('store');
        if ($store) {
            $collection->addFieldtoFilter('main_table.store_id', $store);
        }


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'item_id',
            [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'item_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn(
            'store_id',
            [
                'header'           => __('Purchased from Store'),
                'sortable'         => true,
                'index'            => 'store_id',
                'store_view'       => true,
                'display_deleted'  => true,
                'type'             => 'store',
                'filter_index'     => 'main_table.store_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header'           => __('Purchase Date'),
                'index'            => 'created_at',
                'type'             => 'datetime',
                'filter_index'     => 'main_table.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'billing_name',
            [
                'header'           => __('Bill to Name'),
                'index'            => 'billing_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'shipping_name',
            [
                'header'           => __('Ship to Name'),
                'index'            => 'shipping_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'qty_ordered',
            [
                'header'           => __('Qty'),
                'index'            => 'qty_ordered',
                'type'             => 'number',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'base_row_total',
            [
                'header'           => __('Row Total (Base)'),
                'index'            => 'base_row_total',
                'type'             => 'currency',
                'currency_code'    => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'column_css_class' => 'col-id',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/soldItemsGrid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
