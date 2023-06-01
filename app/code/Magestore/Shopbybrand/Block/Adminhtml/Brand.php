<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml;

/**
 * Grid Container Brand
 */
class Brand extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'Magestore_Shopbybrand';
        $this->_headerText = __('Brand Grid');
        $this->_addButtonLabel = __('Add New Brand');

        parent::_construct();
        $this->addButton('import_brand', [
            'label'   => __('Import Brands'),
            'onclick' => "setLocation('{$this->getUrl('*/*/importbrand')}')",
            'class'   => 'add',
        ], -1);
    }
}
