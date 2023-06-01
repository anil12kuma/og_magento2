<?php

namespace Wishtech\ProductZoom\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class CheckboxSwitch
 * @package Wishtech\ProductZoom\Block\Adminhtml\System\Config
 */
abstract class CheckboxSwitch extends \Magento\Config\Block\System\Config\Form\Field
{
    static $_isCssUsed = false;
    static $_isJsUsed = false;

    protected $invert = false;

    /**
     * @return string
     */
    private function _getCss()
    {
        if (self::$_isCssUsed)  {
            return '';
        }
        self::$_isCssUsed = true;
        return '<style>
    .onoffswitch {
    position: relative; width: 56px;
    -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
}
.onoffswitch-checkbox {
    position: absolute !important;
    opacity: 0;
    pointer-events: none;
}
.onoffswitch-label {
    display: block; overflow: hidden; cursor: pointer;
    border: 2px solid #fff; border-radius: 20px;
}
.onoffswitch-inner {
    display: block; width: 200%; margin-left: -100%;
    transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner:before, .onoffswitch-inner:after {
    display: block; float: left; width: 50%; height: 30px; padding: 0; line-height: 30px;
    font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
    box-sizing: border-box;
}
.onoffswitch-inner:before {
    content: "";
    padding-left: 10px;
    background-color: #33CC98; color: #FFFFFF;
}
.onoffswitch-inner:after {
    content: "";
    padding-right: 10px;
    background-color: #EEEEEE; color: #999999;
    text-align: right;
}
.onoffswitch-switch {
    display: block; width: 23px; margin: 1.5px;
    background: #FFFFFF;
    position: absolute; top: 1px; bottom: 0;
    right: 22px;
    border: 2px solid #fff; border-radius: 20px;
    transition: all 0.3s ease-in 0s;
	height:23px;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
    margin-left: 0;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
    right: 2px;
	top: 1px;
}

.onoffswitch-value { display: none!important; }
</style>';
    }

    /**
     * @return string
     */
    private function _getJs()
    {
        if (self::$_isJsUsed) {
            return '';
        }
        self::$_isJsUsed = true;
	return '
<script type="text/javascript">//<![CDATA[
    function updateOnOffSwitch(el)
    {
	var useid = jQuery(el).attr("data-useid");

	if (jQuery(el).hasClass("notcheck"))
	{
	    var val = jQuery(el).attr("checked") ? "0" : "1";

	}
	else
	{
	    var val = jQuery(el).attr("checked") ? "1" : "0";

	}
	document.getElementById(useid).value = val;


	if(document.createEventObject)
	{
	  var evt = document.createEventObject();
	  document.getElementById(useid).fireEvent("onchange", evt);
	}
	else
	{
	  var evt = document.createEvent("HTMLEvents");
	  evt.initEvent("change", false, true);
	  document.getElementById(useid).dispatchEvent(evt);
	}
    }

                require(["jquery"], function(jQuery)
                {
                    jQuery(document).ready( function()
                    {
                        jQuery(".onoffswitch-value").each(function()
                        {
                            jQuery(this).attr("checked","checked");
                        })
                    });

                })
//]]></script>';
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->addClass('onoffswitch-value');
        if ($this->invert)
        {
	    $invertClass = 'onoffswitch-checkbox-invert' ;
	    $checked = (bool)$element->getValue() ? '' : 'checked="checked"';
        }
        else
        {
	    $invertClass = '';
	    $checked = (bool)$element->getValue() ? 'checked="checked"' :'';
        }
        $element->setType('checkbox');
        $element->setChecked(true);
        return $this->_getCss() . $this->_getJs() . '

				<div class="onoffswitch">
		    <input id="'.$element->getId().'_onoffswitch" class="onoffswitch-checkbox ' . $invertClass .'" type="checkbox" onChange="updateOnOffSwitch(this)" data-useid="'.$element->getId().'"  '.$checked.' />
                    <label class="onoffswitch-label" for="'.$element->getId().'_onoffswitch">
                        <span class="onoffswitch-inner"></span>
						<span class="onoffswitch-switch"></span>
                    </label>

                      '.$element->getElementHtml().'
                </div>';
    }

    abstract public function getOnLabel();
    abstract public function getOffLabel();
}
