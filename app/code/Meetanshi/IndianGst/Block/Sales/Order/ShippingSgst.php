<?php

namespace Meetanshi\IndianGst\Block\Sales\Order;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Meetanshi\IndianGst\Helper\Data as HelperData;

/**
 * Class ShippingSgst
 * @package Meetanshi\IndianGst\Block\Sales\Order
 */
class ShippingSgst extends Template
{
    /**
     * @var HelperData
     */
    protected $helper;
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * ShippingSgst constructor.
     * @param Context $context
     * @param HelperData $helper
     * @param Currency $currency
     * @param array $data
     */
    public function __construct(Context $context, HelperData $helper, Currency $currency, array $data = [])
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $order = $this->getOrder();
        $this->getSource();

        if (!$this->getSource()->getShippingSgstAmount() || $this->getSource()->getShippingSgstAmount() <= 0) {
            return $this;
        }

        $shippingSgstAmount = $this->getSource()->getShippingSgstAmount();
        $baseShippingSgstAmount = $this->getSource()->getShippingSgstAmount() / $order->getBaseToOrderRate();

        $total = new DataObject(['code' => 'shipping_sgst_amount',
            'value' => $shippingSgstAmount,
            'base_value' => $baseShippingSgstAmount,
            'label' => 'Shipping SGST']);

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
