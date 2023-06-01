<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog\Entity\Attribute;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class SaveAfter extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $requestOb = $this->_objectManager->get('Magento\Framework\App\Request\Http');
        $fullActionControllerName = $requestOb->getModuleName().'/'.$requestOb->getControllerName().'/'.$requestOb->getActionName();

        $actionForDisable = [
            'shopbybrand/brand/save',
            'shopbybrand/brand/importProcess'
        ];
        if(in_array($fullActionControllerName, $actionForDisable)){
            return;
        }
        
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand $resource */
        $resource = $this->_brandFactory->create()->getResource();
        $attribute = $observer->getAttribute();
        $attributeCode = $this->_brandHelper->getAttributeCode();
        if ($attribute->getAttributeCode() == $attributeCode) {
            $OldAttributeValue = $this->getOldAttributeValue($attributeCode);
            $getAllBrandOptionId = $this->getAllBrandOptionId();
            foreach ($getAllBrandOptionId as $key => $value) {
                unset($OldAttributeValue[$key]);
            }
            unset($OldAttributeValue['']);
            foreach ($OldAttributeValue as $id => $option) {
                if (intval($id) == 0) {
                    $optionDatabase = $resource->getAttributeOptions($option);
                    $optionDatabase = $optionDatabase[0];
                    if ($optionDatabase['option_id']) {
                        $id = $optionDatabase['option_id'];
                    }
                }
                $op['store_id'] = 0;
                $op['option_id'] = $id;
                $op['value'] = $option;
                $this->_brandHelper->insertBrandFromOption($op);
            }
        }
    }

    /**
     * @return array
     */
    public function getAllBrandOptionId()
    {
        $brands = $this->_brandFactory->create()->getCollection();
        $array = [];
        foreach ($brands as $item) {
            $array[$item->getOptionId()] = $item->getName();
        }

        return $array;
    }
}