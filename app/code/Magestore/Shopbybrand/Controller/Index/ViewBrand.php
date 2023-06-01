<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Pdfinvoiceplus
 * @module   Pdfinvoiceplus
 * @author   Magestore Developer
 */
class ViewBrand extends \Magento\Framework\App\Action\Action
{
    /**
     * Execute action
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Shopbybrand\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magestore\Shopbybrand\Model\Brand
     */
    protected $_brandFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * ViewBrand constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Shopbybrand\Helper\Data $helper
     * @param \Magestore\Shopbybrand\Model\SystemConfig $systemConfig
     * @param \Magestore\Shopbybrand\Model\Brand $brandFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Shopbybrand\Helper\Data $helper,
        \Magestore\Shopbybrand\Model\SystemConfig $systemConfig,
        \Magestore\Shopbybrand\Model\Brand $brandFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_systemConfig = $systemConfig;
        $this->_brandFactory = $brandFactory;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return \Magestore\Shopbybrand\Model\Brand
     */
    public function _initBrand()
    {
        $brandId = (int)$this->getRequest()->getParam('brand_id', false);
        if (!$brandId) {
            return false;
        }
        try {
            $brand = $this->_brandFactory->load($brandId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        $this->_coreRegistry->register('current_brand', $brand);

        return $brand;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $systemConfigObj = $this->_objectManager->create('\Magestore\Shopbybrand\Model\SystemConfig');
        $onlyBrandHaveProduct = $systemConfigObj->isDisplayBrandHaveProduct();
        $brand = $this->_initBrand();
        $productIds = $brand->getProductIdsByBrandId($brand->getId());
        if(!$this->_systemConfig->isEnable() || !$productIds && $onlyBrandHaveProduct) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('csm/noroute');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->layerResolver->create('category');

        if (!$this->_coreRegistry->registry('current_category')) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $category->load($this->_storeManager->getStore()->getRootCategoryId());
            $category->setName($brand->getBrandName());
            $this->_coreRegistry->register('current_category', $category);
        }
        $resultPage->getConfig()->getTitle()->set( !empty($brand->getTitle()) ? $brand->getTitle() : $brand->getBrandName() );
        $resultPage->getConfig()->setPageLayout($this->_systemConfig->getBrandDetailPageLayout());

        return $resultPage;
    }
}