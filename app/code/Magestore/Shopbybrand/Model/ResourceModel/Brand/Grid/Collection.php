<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\ResourceModel\Brand\Grid;

class Collection extends \Magestore\Shopbybrand\Model\ResourceModel\Brand\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
