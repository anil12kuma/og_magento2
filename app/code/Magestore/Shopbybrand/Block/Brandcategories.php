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
class Brandcategories extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::brandcategories.phtml';

    /**
     * @var \Magestore\Shopbybrand\Helper\Brand
     */
    protected $_helper;

    /**
     * Brandcategories constructor.
     *
     * @param Context $context
     * @param \Magestore\Shopbybrand\Helper\Brand $helper
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Helper\Brand $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getAllCategories()
    {
        return $this->_helper->getParentCategories();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        return $storeId;
    }
}
