<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Widget\Form\Renderer\Fieldset;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Identifier of default global field
     * used for loading data of default scope
     */
    const DEFAULT_GLOBAL_FIELD = ['id','url_key','title','banner_url','position_brand','banner','logo'];

    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::widget/form/renderer/fieldset/element.phtml';

    /**
     * Retrieve data object related with form
     *
     * @return \Magento\Catalog\Model\Product || \Magento\Catalog\Model\Category
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     *
     * @return string
     */
    public function getElementName()
    {
        return $this->getElement()->getName();
    }

    /**
     * @return string
     */
    public function getElementStoreViewId()
    {
        return $this->getElement()->getStoreId();
    }



    /**
     * Check default value usage fact
     *
     * @return bool
     */
    public function usedDefault()
    {
        return $this->getElementStoreViewId() ? false : true;
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @return bool
     */
    public function canDisplayUseDefault()
    {
        if($this->getRequest()->getParam('store')){
            if(!in_array($this->getElementName(),self::DEFAULT_GLOBAL_FIELD)){
                return true;
            }
        }
        return false;
    }

    /**
     * Disable field in default value using case
     *
     * @return $this | \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
     */
    public function checkFieldDisable()
    {
        if (!$this->getElementStoreViewId() && $this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {
        if ($this->getElement()->getDateFormat() != null || in_array($this->getElementName(), self::DEFAULT_GLOBAL_FIELD)) {
            return '[GLOBAL]';
        }
        return '[STORE VIEW]';
    }

    /**
     * Retrieve element label html
     *
     * @return string
     */
    public function getElementLabelHtml()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if (!empty($label)) {
            $element->setLabel(__($label));
        }
        return $element->getLabelHtml();
    }

    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getElement()->getElementHtml();
    }

    /**
     * Default sore ID getter
     * @return integer
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

}
