<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Model\Source;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string[]
     */
    protected $stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING
    ];

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->orderConfig->getStateStatuses($this->stateStatuses);

        $options = [];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }
}
