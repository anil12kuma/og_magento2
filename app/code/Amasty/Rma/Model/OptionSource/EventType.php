<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class EventType implements OptionSourceInterface
{
    const RMA_CREATED = 0;
    const TRACKING_NUMBER_ADDED = 1;
    const TRACKING_NUMBER_DELETED = 2;
    const NEW_MESSAGE = 3;
    const DELETED_MESSAGE = 4;
    const CUSTOMER_CLOSED_RMA = 5;
    const STATUS_AUTOMATICALLY_CHANGED = 6;
    const MANAGER_SAVED_RMA = 7;
    const MANAGER_ADDED_SHIPPING_LABEL = 8;
    const MANAGER_DELETED_SHIPPING_LABEL = 9;
    const SYSTEM_CHANGED_STATUS = 10;
    const SYSTEM_CHANGED_MANAGER = 11;

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
            self::RMA_CREATED => __('Rma Created'),
            self::TRACKING_NUMBER_ADDED => __('Tracking Number Added'),
            self::TRACKING_NUMBER_DELETED => __('Tracking Number Deleted'),
            self::NEW_MESSAGE => __('New Message'),
            self::DELETED_MESSAGE => __('Deleted Message'),
            self::CUSTOMER_CLOSED_RMA => __('Customer Closed Rma'),
            self::STATUS_AUTOMATICALLY_CHANGED => __('Status Automatically Changed'),
            self::MANAGER_SAVED_RMA => __('Manager Saved Rma'),
            self::MANAGER_ADDED_SHIPPING_LABEL => __('Manager Added Shipping Label'),
            self::MANAGER_DELETED_SHIPPING_LABEL => __('Manager Deleted Shipping Label'),
            self::SYSTEM_CHANGED_STATUS => __('System Changed Status'),
            self::SYSTEM_CHANGED_MANAGER => __('System Changed Manager')
        ];
    }
}
