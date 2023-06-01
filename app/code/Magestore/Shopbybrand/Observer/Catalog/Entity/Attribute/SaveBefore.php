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
class SaveBefore extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magestore\Shopbybrand\Model\ResourceModel\Brand $resource */
        $resource = $this->_objectManager->get('Magestore\Shopbybrand\Model\ResourceModel\Brand');
        $attribute = $observer->getAttribute();
        $attributeCode = $this->_brandHelper->getAttributeCode();

        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $stores */
        $stores = $this->_storeCollectionFactory->create();
        $stores->addFieldToFilter('is_active', 1)->addFieldToFilter('store_id', ['neq' => 0]);

        if ($attribute->getAttributeCode() == $attributeCode) {
            $optionValue = $attribute->getOption();
            $options = $optionValue['value'];
            $deletes = (isset($optionValue['delete']) ? $optionValue['delete'] : []);

            $OldAttributeValue = $this->getOldAttributeValue($attributeCode);

            foreach ($options as $id => $option) {
                if (intval($id) == 0) {
                    $optionDatabase = $resource->getAttributeOptions($option[0]);
                    if(isset($optionDatabase[0]) && $optionDatabase[0] !=null){
                        $optionDatabase = $optionDatabase[0];
                        if ($optionDatabase['option_id']) {
                            $id = $optionDatabase['option_id'];
                        }
                    }
                }

                $brand = $this->_brandFactory->create()->load($id, 'option_id');
                if (isset($deletes[$id]) && $deletes[$id]) {
                    if ($brand->getId()) {
                        foreach ($stores as $store) {
                            $urlRewrite = $this->_objectManager->create('Magestore\Shopbybrand\Model\Urlrewrite')->loadByRequestPath($brand->getUrlKey() , $store->getId());
                            if ($urlRewrite->getId()) {
                                $urlRewrite->delete();
                            }
                        }
                        $brand->delete();
                        continue;
                    }
                } else {
                    $op['store_id'] = 0;
                    if(isset($op['option_id']) && $op['option_id']!=null){
                        $op['option_id'] = $id;
                        $op['value'] = $option[0];
                        if (isset($option[0]) && $OldAttributeValue[$id] != $option[0] && isset($OldAttributeValue[$id])) {
                            $this->_brandHelper->insertBrandFromOption($op);
                        }
                    }

                    foreach ($stores as $store) {
                        if (isset($option[$store->getId()]) && $option[$store->getId()]) {
                            $opStore['store_id'] = $store->getId();
                            $opStore['option_id'] = $id;
                            $opStore['value'] = $option[$store->getId()];

                            if (!$brand->getId()) {
                                $brand = $this->_brandFactory->create()->load($id, 'option_id');
                            }

                            if ($brand->getId()) {
                                $brandValue = $this->_brandValueFactory->create();
                                $brandValue->loadAttributeValue($brand->getId(), $store->getId(), 'name');

                                if ($brandValue->getValue() != $option[$store->getId()]) {
                                    $brandValue->setData('value', $option[$store->getId()])
                                        ->setStoreId($store->getId())
                                        ->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($stores as $store) {
           $this->_cache->save(serialize(''), 'brand_data_' . $store->getId());
        }
    }
}