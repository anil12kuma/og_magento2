<?php
namespace Gearup\Partner\Block\Adminhtml\Partner\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('partner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Partner Information'));
    }
}