<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class Productinfo extends \Magestore\Shopbybrand\Block\AbstractBlock
{


    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getProduct(){
        $product = $this->_coreRegistry->registry('current_product');
        return $product;
    }

    /**
     * @return mixed
     */
    public function getStoreId(){
        return $this->storeManager()->getStore()->getId();
    }

    /**
     * @return mixed
     */
    public function getBrand(){
        $brand = $this->getObjectManager()->create('Magestore\Shopbybrand\Model\Brand');
        $product = $this->getProduct();
        $attributeCode = $this->getObjectManager()->get('Magestore\Shopbybrand\Helper\Brand')->getAttributeCode();
        if($product->getId()){
            $optionId = $product->getData($attributeCode);
            if($optionId){
                $brand->load($optionId, 'option_id');
                $brand->setStoreId($this->getStoreId())->load($brand->getId());
            }
        }

        if($brand->getIsActive()==1)
            return $brand;
    }


}