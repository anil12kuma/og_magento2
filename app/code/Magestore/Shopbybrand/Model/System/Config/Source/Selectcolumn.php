<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\System\Config\Source;

/**
 * Model Status
 */
class Selectcolumn
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('None')],
            ['value' => 1, 'label' => __('In Left Column')],
            ['value' => 2, 'label' => __('In Right Column')],
        ];
    }
}
