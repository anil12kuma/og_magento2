<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer;

use Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection;
use Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class Context
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
     * @var \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_brandCollectionFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    
    /**
     * Context constructor.
     *
     * @param \Magestore\Shopbybrand\Model\SystemConfig $systemConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magestore\Shopbybrand\Helper\Brand $brandHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magestore\Shopbybrand\Model\SystemConfig $systemConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\Shopbybrand\Helper\Brand $brandHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magestore\Shopbybrand\Model\BrandProductsFactory $brandProductsFactory,
        \Magestore\Shopbybrand\Model\ResourceModel\Brand\CollectionFactory $brandCollectionFactory,
        \Magestore\Shopbybrand\Model\StoreValueFactory $brandValueFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_systemConfig = $systemConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_brandHelper = $brandHelper;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->_brandFactory = $brandFactory;
        $this->_brandProductsFactory = $brandProductsFactory;
        $this->_brandCollectionFactory = $brandCollectionFactory;
        $this->_brandValueFactory = $brandValueFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_cache = $cache;
        $this->_productFactory = $productFactory;
        $this->_request = $request;
    }

    /**
     * @return \Magestore\Shopbybrand\Model\SystemConfig
     */
    public function getSystemConfig()
    {
        return $this->_systemConfig;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getCoreRegistry()
    {
        return $this->_coreRegistry;
    }

    /**
     * @return \Magestore\Shopbybrand\Helper\Brand
     */
    public function getBrandHelper()
    {
        return $this->_brandHelper;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \Magento\Eav\Model\Config
     */
    public function getEavConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * @return \Magestore\Shopbybrand\Model\BrandFactory
     */
    public function getBrandFactory()
    {
        return $this->_brandFactory;
    }

    /**
     * @return \Magestore\Shopbybrand\Model\BrandFactory
     */
    public function getBrandProductsFactory()
    {
        return $this->_brandProductsFactory;
    }

    /**
     * @return \Magestore\Shopbybrand\Model\StoreValueFactory
     */
    public function getBrandValueFactory()
    {
        return $this->_brandValueFactory;
    }

    /**
     * @return \Magento\Framework\App\CacheInterface
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductFactory()
    {
        return $this->_productFactory;
    }

    /**
     * @return CollectionFactory
     */
    public function getBrandCollectionFactory()
    {
        return $this->_brandCollectionFactory;
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    public function getStoreCollectionFactory()
    {
        return $this->_storeCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }
}