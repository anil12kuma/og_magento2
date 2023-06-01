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
class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Shopbybrand\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magestore\Shopbybrand\Helper\Image
     */
    protected $_imageHelper;


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * AbstractBlock constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_systemConfig = $context->getSystemConfig();
        $this->_coreRegistry = $context->getCoreRegistry();
        $this->_imageHelper = $context->getImageHelper();
        $this->_objectManager = $context->getObjectManager();
    }

    /**
     * @return \Magestore\Shopbybrand\Model\SystemConfig
     */
    public function getSystemConfig()
    {
        return $this->_systemConfig;
    }

    /**
     * @return \Magestore\Shopbybrand\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->_imageHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function storeManager()
    {
        return $this->_storeManager;
    }


    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
}
