<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source;

class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    private $groupSource;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->groupSource->getAllOptions();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionArray = $this->toOptionArray();
        $labels =  array_column($optionArray, 'label');
        $values =  array_column($optionArray, 'value');

        return array_combine($values, $labels);
    }
}
