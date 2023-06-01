<?php

namespace Dev\RestApi\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;

class ProductCustAttr
{
     public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
        
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');

        $product = $entity;
        /** Get Current Extension Attributes from Product */
        $extensionAttributes = $product->getExtensionAttributes();
        $baseUrl  = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."catalog/product";
        $extensionAttributes->setBaseUrl($baseUrl); // custom field value set
        $product->setExtensionAttributes($extensionAttributes);
        return $product;
    }

    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            /** Get Current Extension Attributes from Product */
            $extensionAttributes = $entity->getExtensionAttributes();
            $baseUrl  = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."catalog/product";
            $extensionAttributes->setBaseUrl($baseUrl); // custom field value set
            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }
}