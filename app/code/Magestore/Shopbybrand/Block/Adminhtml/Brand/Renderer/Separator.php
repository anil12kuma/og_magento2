<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Renderer;

/**
 * Image renderer.
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Separator extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $html = '<tr id="row_' . $id . '">'
            . '<td class="label" colspan="3">';
        $marginTop = $element->getComment() ? $element->getComment() : '0px';
        $html .= '<div style="margin-top: ' . $marginTop
            . '; font-weight: bold; border-bottom: 1px solid #dfdfdf; text-align: left;">';
        $html .= $element->getLabel();
        $html .= '</div></td></tr>';

        return $html;
    }
}