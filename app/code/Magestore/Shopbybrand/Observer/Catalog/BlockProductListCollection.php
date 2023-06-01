<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Observer\Catalog;

use Magento\Framework\Event\Observer;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class BlockProductListCollection extends \Magestore\Shopbybrand\Observer\AbstractObserver
{
    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection */
        $collection = $observer->getData('collection');

        if (!$this->_coreRegistry->registry('is_join_position')) {
            $route = $this->getRequest()->getRouteName();
            $params = $this->getRequest()->getParams();
            if (isset($params['brand_id'])) {
                if (!isset($params['product_list_order'])) {
                    $params['product_list_order'] = '';
                }
                if (($route == 'brand') && ($params['product_list_order'] == null || $params['product_list_order'] == 'position')) {
                    if (!isset($params['dir'])) {
                        $params['dir'] = '';
                    }
                    $dir = ($params['dir'] != 'desc') ? 'asc' : 'desc';

                    $collection
                        ->getSelect()
                        ->joinLeft(
                            ['ms_brand_products' => $collection->getTable('ms_brand_products')],
                            "e.entity_id = ms_brand_products.product_id",
                            [
                                'position' => 'ms_brand_products.position',
                            ]
                        )
                        ->order('ms_brand_products.position ' . $dir);
                }
            }

            $this->_coreRegistry->register('is_join_position', '1');
        }
    }
}