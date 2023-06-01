<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Block;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;

class Button extends Template
{
    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var \Amasty\CancelOrder\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\CancelOrder\Model\Validation
     */
    private $validation;

    public function __construct(
        Template\Context $context,
        \Amasty\CancelOrder\Model\Validation $validation,
        \Amasty\CancelOrder\Model\ConfigProvider $configProvider,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->validation = $validation;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->isButtonAvailable()) {
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getButtonTitle()
    {
        return $this->configProvider->getButtonTitle();
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return $this->configProvider->isPopupEnabled();
    }

    /**
     * @return string
     */
    public function getCancelOrderUrl()
    {
        return $this->getUrl('am_cancel_order/cancel/process', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * @return bool
     */
    protected function isButtonAvailable()
    {
        return $this->validation->validateOrderAndSettings($this->getOrder());
    }

    /**
     * Set order
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->registry->registry('current_order');
        }

        return $this->order;
    }
}
