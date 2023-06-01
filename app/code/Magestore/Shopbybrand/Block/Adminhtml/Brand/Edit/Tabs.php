<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brand_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('New Brand'));
    }

    protected function _beforeToHtml()
    {
//        $this->addTab(
//            'Gift Items',
//            [
//                'label' => __('Gift Items'),
//                'title' => __('Gift Items'),
//                'class'     => 'ajax',
//                'url'       => $this->getUrl('*/*/gift', array('_current' => true)),
//                'content' => $this->getChildHtml('main'),
//                'active' => true
//
//            ]
//        );
        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // add sold items tab
        if ($this->getRequest()->getParam('id')) {
            $this->_activeTab = 'main_section';
            $this->addTabAfter(
                'sold_items',
                [
                    'label'   => 'Sold Items',
                    'title'   => 'Sold Items',
                    'content' => $this->getLayout()->createBlock(
                        '\Magestore\Shopbybrand\Block\Adminhtml\Brand\Edit\Tab\SoldItems',
                        'deal.product.grid'
                    )->toHtml(),
                ],
                'product_section'
            );
        }

        return $this;
    }
}
