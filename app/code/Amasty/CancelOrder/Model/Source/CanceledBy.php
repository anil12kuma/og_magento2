<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source;

class CanceledBy implements \Magento\Framework\Option\ArrayInterface
{
    const BY_CUSTOMER = 0;
    const AUTO_CANCEL = 1;

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::BY_CUSTOMER => __('Customer'),
            self::AUTO_CANCEL => __('Auto Cancel')
        ];
    }

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BY_CUSTOMER,
                'label' => __('Customer')
            ],
            [
                'value' => self::AUTO_CANCEL,
                'label' => __('Auto Cancel')
            ]
        ];
    }
}
