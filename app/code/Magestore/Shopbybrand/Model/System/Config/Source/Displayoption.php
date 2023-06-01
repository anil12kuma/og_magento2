<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\System\Config\Source;

/**
 * Model Status
 */
class Displayoption
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Logo and name')],
            ['value' => 1, 'label' => __('Only name')],
            ['value' => 2, 'label' => __('Only logo')],
        ];
    }
}
