<?php

namespace Amasty\Rules\Block\Adminhtml\System\Config;

/**
 * Custom notice block.
 */
class Notice extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getHtml()
    {
        return '<div name="' . $this->getName() . '" class="message message-info info">' . $this->getLabel() . '</div>';
    }
}
