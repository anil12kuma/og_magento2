<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var \Magestore\Shopbybrand\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_brandHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandProductsFactory
     */
    protected $_brandProductsFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_brandCollectionFactory;

    /**
     * @var \Magestore\Shopbybrand\Model\StoreValueFactory
     */
    protected $_brandValueFactory;
    
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * AbstractObserver constructor.
     *
     * @param Context $context
     */
    public function __construct(\Magestore\Shopbybrand\Observer\Context $context)
    {
        $this->_systemConfig = $context->getSystemConfig();
        $this->_coreRegistry = $context->getCoreRegistry();
        $this->_brandHelper = $context->getBrandHelper();
        $this->_objectManager = $context->getObjectManager();
        $this->_storeManager = $context->getStoreManager();
        $this->_eavConfig = $context->getEavConfig();
        $this->_brandFactory = $context->getBrandFactory();
        $this->_brandProductsFactory = $context->getBrandProductsFactory();
        $this->_brandCollectionFactory = $context->getBrandCollectionFactory();
        $this->_brandValueFactory = $context->getBrandValueFactory();
        $this->_cache = $context->getCache();
        $this->_productFactory = $context->getProductFactory();
        $this->_storeCollectionFactory = $context->getStoreCollectionFactory();
        $this->_request = $context->getRequest();
    }

    /**
     * @param $attributename
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOldAttributeValue($attributename)
    {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributename);
        $attributeArray = [];
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            $attributeArray[$option['value']] = $option['label'];
        }

        return $attributeArray;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }
}