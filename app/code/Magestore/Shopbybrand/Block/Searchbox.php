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
class Searchbox extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::searchbox.phtml';

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * Searchbox constructor.
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
    public function getAllBrands()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $shopbybrands = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection')
            ->setStoreId($storeId)
            ->setOrder('sort_order', 'ASC');

        return $shopbybrands;
    }

    /**
     * @return array
     */
    public function getSearchData()
    {
        $shopbybrands = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand')->getBrandCollection();
        $array = [];
        foreach ($shopbybrands as $brand) {
            $array[] = [
                'n' => $brand->getBrandName(),
                'k' => $brand->getUrlKey(),
            ];
        }

        return $array;
    }
}
