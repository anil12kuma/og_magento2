<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source;

class Refund implements \Magento\Framework\Option\ArrayInterface
{
    const ONLINE = 'online';
    const OFFLINE = 'offline';
    const DISABLED = 'disabled';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => self::ONLINE,
                'label' => __('Online (if possible)')
            ],
            [
                'value' => self::OFFLINE,
                'label' => __('Offline')
            ]
        ];
    }
}
