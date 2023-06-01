<?php
namespace Wishtech\ProductZoom\Block\System\Config\Form\Field;
/**
 * Class Color
 * @package Wishtech\ProductZoom\Block\System\Config\Form\Field
 */
class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Color constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function (e) {
                    var $el = $("#' . $element->getHtmlId() . '");
                    $el.css("backgroundColor", "'. $value .'");

                    // Attach the color picker
                    $el.ColorPicker({
                        color: "'. $value .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';
        return $html;
    }
}
