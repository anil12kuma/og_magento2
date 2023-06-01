<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\System\Config;

/**
 * class AddJsColor
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class AddJsColor extends \Magento\Config\Block\System\Config\Form\Fieldset
{

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '
            <script>
                require(["Magestore_Shopbybrand/js/jscolor.min"],function($){});
            </script>
        ';
        return $html;
    }
}