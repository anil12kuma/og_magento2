<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to samparker801@gmail.com so we can send you a copy immediately.
 *
 * @category    Mega
 * @package     Mega_PhoneLogin
 * @copyright   Copyright (c) 2017 Sam Parker (samparker801@gmail.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mega\Phonelogin\Block\Adminhtml\Smslog;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_smsLogFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Mega\Phonelogin\Model\SmsLogFactory $smsLogFactory,
        array $data = []
    )
    {
        $this->_smsLogFactory = $smsLogFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('smslogGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
       // $this->setUseAjax(true);
    }


    public function _prepareCollection()
    {
        $collection = $this->_smsLogFactory
            ->create()
            ->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    public function _prepareColumns()
    {
        $this->addColumn('id', [
                'header' => __('ID'),
                'align' => 'right',
                'index' => 'id',
            ]
        );

        $this->addColumn('mobile_number', [
                'header' => __('Mobile Number'),
                'align' => 'right',
                'index' => 'mobile_number',
            ]
        );

        $this->addColumn('text', [
                'header' => __('Message'),
                'align' => 'right',
                'index' => 'text',
            ]
        );

        $this->addColumn('status', [
                'header' => __('Status'),
                'align' => 'right',
                'index' => 'status',
                'type' => 'options',
                'options' => array('Yes', 'No')
            ]
        );
        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {

    }


    protected function _prepareMassaction()
    {
        return $this;
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete12',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('catalog/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }


}