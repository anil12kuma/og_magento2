<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source\AutoCancelMode;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    const PENDING = 'pending';
    const PENDING_PAYMENT = 'pending_payment';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => self::PENDING_PAYMENT,
                'label' => __('Pending Payment')
            ]
        ];
    }
}
