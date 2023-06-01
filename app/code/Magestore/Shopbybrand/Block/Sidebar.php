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
class Sidebar extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * @var null
     */
    protected $_brandCollection = null;

    /**
     * Sidebar constructor.
     *
     * @param Context $context
     * @param \Magestore\Shopbybrand\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getBrandSort()
    {
        return $this->_objectManager->create('Magestore\Shopbybrand\Block\Brand')->getBrandsData();
    }

    /**
     * @return mixed
     */
    public function getBrandsData()
    {
        return $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->getBrandsData();
    }

    /**
     * @return \Magestore\Shopbybrand\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return mixed
     */
    public function getMaximumSidebar()
    {
        return $this->getSystemConfig()->getNumOfBrands();
    }

    /**
     * @param $url_key
     *
     * @return string
     */
    public function getBrandUrl($url_key)
    {
        $url = $this->getUrl($url_key, []);

        return $url;
    }

    /**
     * @return mixed
     */
    public function getDisplayModule()
    {
        return $this->getSystemConfig()->isEnable();
    }

    /**
     * @return mixed
     */
    public function getDisplaySidebar()
    {
        return $this->getSystemConfig()->isDisplaySidebar();
    }

    /**
     * @return mixed
     */
    public function getOptionDisplay()
    {
        return $this->getSystemConfig()->optionDisplay();
    }
}