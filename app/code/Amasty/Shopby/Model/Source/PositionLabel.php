<?php

namespace Amasty\Shopby\Model\Source;

class PositionLabel implements \Magento\Framework\Option\ArrayInterface
{
    public const POSITION_BEFORE = 0;
    public const POSITION_AFTER = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_BEFORE,
                'label' => __('Before')
            ],
            [
                'value' => self::POSITION_AFTER,
                'label' => __('After')
            ]
        ];
    }
}
