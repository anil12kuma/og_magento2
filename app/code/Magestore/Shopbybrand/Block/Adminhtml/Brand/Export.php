<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand;

/**
 * Grid Grid
 */
class Export extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;
    protected $_storeField = ['brand_name', 'is_featured', 'title', 'is_active',];
    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory
     */
    protected $_brandValueResource;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory $brandValueFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
        $this->_brandValueResource = $brandValueFactory;

    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('Grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $collection = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection');

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
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'brand_name',
            [
                'header'           => __('Name'),
                'index'            => 'brand_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'sort_order',
            [
                'header'           => __('Sort Order'),
                'index'            => 'sort_order',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'url_key',
            [
                'header'           => __('URL Key'),
                'index'            => 'url_key',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'title',
            [
                'header'           => __('Page Title'),
                'index'            => 'title',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'is_featured',
            [
                'header'           => __('Is Featured'),
                'index'            => 'is_featured',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'is_active',
            [
                'header'  => __('Status'),
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => [1 => __('1'), 2 => __('2')]
            ]
        );
        $this->addColumn(
            'short_description',
            [
                'header'           => __('Short Description'),
                'index'            => 'short_description',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'description',
            [
                'header'           => __('Description'),
                'index'            => 'description',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'meta_keywords',
            [
                'header'           => __('Meta Keywords'),
                'index'            => 'meta_keywords',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );
        $this->addColumn(
            'meta_description',
            [
                'header'           => __('Meta Description'),
                'index'            => 'meta_description',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        return parent::_prepareColumns();
    }
}
