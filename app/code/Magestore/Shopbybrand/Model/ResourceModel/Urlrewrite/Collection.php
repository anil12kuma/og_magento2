<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\ResourceModel\Urlrewrite;

/**
 * Collection Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\Urlrewrite', 'Magestore\Shopbybrand\Model\ResourceModel\Urlrewrite');
    }
}
