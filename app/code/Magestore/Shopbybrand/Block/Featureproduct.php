<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shopbybrand Block
 *
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
namespace Magestore\Shopbybrand\Block;

class Featureproduct extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::featureproduct.phtml';

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * Featureproduct constructor.
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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFeaturedProducts()
    {
        return $this->_helper->getFeaturedProducts();
    }

    /**
     * @param $product
     *
     * @return mixed
     */
    public function getProductUrl($product)
    {
        return $product->getProductUrl();
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getItem($id)
    {
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);

        return $product;
    }
}

