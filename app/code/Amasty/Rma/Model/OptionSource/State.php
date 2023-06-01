<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class State implements ArrayInterface
{
    const PENDING = 0;
    const AUTHORIZED = 1;
    const RECEIVED = 2;
    const RESOLVED = 3;
    const CANCELED = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::PENDING => __('Processing'),
            self::AUTHORIZED => __('Approved'),
            self::RECEIVED => __('Delivered'),
            self::RESOLVED => __('Completed'),
            self::CANCELED => __('Canceled')
        ];
    }
}
