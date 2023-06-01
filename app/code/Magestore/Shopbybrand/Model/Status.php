<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

/**
 * Model Status
 */
class Status
{
    const STATUS_ENABLED = '1';

    const STATUS_DISABLED = '2';

    /**
     * Get available statuses.
     *
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
