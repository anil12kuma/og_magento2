<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Brand collection model factory
     *
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_brandsFactory;
    protected $_storeField = ['brand_name', 'is_featured', 'title', 'is_active',];
    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory
     */
    protected $_brandValueResource;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory $brandsFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory $brandValueFactory,
        array $data = []
    )
    {
        $this->_brandsFactory = $brandsFactory;
        $this->_brandValueResource = $brandValueFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brandGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_brandsFactory->create();
        $storeId = $this->getRequest()->getParam('store');
        //$collection->setStoreId($storeId);
        if($storeId){
            //@@TODO gop chung collection tai mot noi
            $storeField = (isset($array) && count($array)) ? $array : $this->_storeField;
            foreach ($storeField as $value) {
                $brandValue = $this->_brandValueResource->create()
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('code', $value)
                    ->getSelect()
                    ->assemble();
                $collection->getSelect()
                    ->joinLeft(
                        ['brand_value_' . $value => new \Zend_Db_Expr("($brandValue)"),],
                        'main_table.id = brand_value_' . $value . '.brand_id',
                        [$value => 'IF(brand_value_' . $value . '.value IS NULL,main_table.' . $value . ',brand_value_' . $value . '.value)']
                    );
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'brand_name',
            [
                'header' => __('Name'),
                'index' => 'brand_name',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-brand_name',
                'column_css_class' => 'col-brand_name'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'sort_order',
            [
                'header' => __('Sort Order'),
                'index' => 'sort_order',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'is_featured',
            [
                'header'           => __('Is Featured'),
                'index'            => 'is_featured',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
                'type'    => 'options',
                'options' => ['1' => __('Yes'), '2' => __('No')],
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'type' => 'options',
                'options' => [1 => __('Enabled'), 2 => __('Disabled')],
                'index' => 'is_active'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass actions
     *
     * @return void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'update_status',
            [
                'label' => __('Update Status'),
                'url' => $this->getUrl(
                    '*/*/massUpdateStatus'
                ),
                'additional' => [
                    'status' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => [
                            1 => __('Enable'),
                            2 => __('Disable')
                        ],
                    ],
                ]
            ]
        );

        $this->getMassactionBlock()->addItem(
            'update_featured',
            [
                'label' => __('Update Featured'),
                'url' => $this->getUrl(
                    '*/*/massUpdateFeatured'
                ),
                'additional' => [
                    'is_featured' => [
                        'name' => 'is_featured',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Is Featured'),
                        'values' => [
                            1 => __('Yes'),
                            2 => __('No')
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
