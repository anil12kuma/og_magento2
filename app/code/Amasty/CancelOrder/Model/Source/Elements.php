<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source;

class Elements implements \Magento\Framework\Option\ArrayInterface
{
    const COMMENT = 'comment';
    const REASON = 'reason';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::COMMENT,
                'label' => __('Comment')
            ],
            [
                'value' => self::REASON,
                'label' => __('Reason')
            ]
        ];
    }
}
