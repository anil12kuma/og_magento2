<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model;

use Amasty\CancelOrder\Model\Source\Refund;
use Magento\Sales\Api\Data\OrderInterface;

class Validation
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\CancelOrder\Model\ConfigProvider $configProvider
    ) {
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function validateOrderAndSettings(OrderInterface $order)
    {
        return $this->configProvider->isEnabled() && $this->validateCustomer() && $this->validateOrder($order);
    }

    /**
     * @return bool
     */
    protected function validateCustomer()
    {
        return in_array($this->getCustomerGroupId(), $this->configProvider->getEnabledCustomerGroups());
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    protected function validateOrder(OrderInterface $order)
    {
        $type = $this->configProvider->getRefundType();

        return in_array($order->getStatus(), $this->configProvider->getEnabledOrderStatuses())
            && $order->getCustomerId() == $this->getCustomerId()
            && (($type !== Refund::DISABLED)
                || ($type == Refund::DISABLED && !$order->canCreditmemo())
            );
    }

    /**
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }

    /**
     * @return int
     */
    protected function getCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId();
    }
}
