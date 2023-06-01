<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand;

/**
 * Form containerEdit
 */
class Import extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magestore_Shopbybrand';
        $this->_controller = 'adminhtml_brand';
        $this->_mode = 'import';
        parent::_construct();
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->updateButton('save', 'label', __('Import Brands'));
    }

    public function getHeaderText()
    {
        return __('Import Brands');
    }
}
