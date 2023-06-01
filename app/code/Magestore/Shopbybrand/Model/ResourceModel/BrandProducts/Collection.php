<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\ResourceModel\BrandProducts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\BrandProducts', 'Magestore\Shopbybrand\Model\ResourceModel\BrandProducts');
    }

}
