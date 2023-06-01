<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\System\Config\Source;

/**
 * Model Status
 */
class Branddetailtemplate
{
    public function toOptionArray()
    {
        return [
            ['value' => '1column', 'label' => '1 Column'],
            ['value' => '2columns-left', 'label' => '2 Columns Left'],
            ['value' => '2columns-right', 'label' => '2 Columns Right'],
            ['value' => '3columns', 'label' => '3 Columns'],
        ];
    }
}
