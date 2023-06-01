<?php
namespace Gearup\Athelete\Block\Adminhtml\Athelete\Edit;

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
        $this->setId('athelete_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Athelete Information'));
    }
}