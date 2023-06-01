<?php
namespace Wishtech\ProductZoom\Block\Adminhtml\System\Config\CheckboxSwitch;
/**
 * Class EnableDisable
 * @package Wishtech\ProductZoom\Block\Adminhtml\System\Config\CheckboxSwitch
 */
class EnableDisable extends \Wishtech\ProductZoom\Block\Adminhtml\System\Config\CheckboxSwitch
{
    /**
     * @return \Magento\Framework\Phrase
     */
    public function getOnLabel()
    {
        return __('On');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getOffLabel()
    {
        return __('Off');
    }
}
