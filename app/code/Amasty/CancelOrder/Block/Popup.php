<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Block;

use Amasty\CancelOrder\Model\ConfigProvider;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;

class Popup extends Template
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var ConfigProvider
     */
    private $config;

    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        ConfigProvider $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return array
     */
    public function getReasons()
    {
        return $this->config->getReasons();
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        return $this->config->getNotice();
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getParentBlock()->getCancelOrderUrl();
    }

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->order;
    }

    /**
     * @param OrderInterface $order
     *
     * @return $this
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param string $element
     *
     * @return bool
     */
    public function isElementRequired(string $element)
    {
        return $this->config->isElementRequired($element);
    }
}
