<?php

namespace Meetanshi\IndianGst\Block\Sales\Invoice;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Invoice\Totals;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;

/**
 * Class ShippingCgst
 * @package Meetanshi\IndianGst\Block\Sales\Order
 */
class ShippingCgst extends Totals
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
     * ShippingCgst constructor.
     * @param Context $context
     * @param HelperData $helper
     * @param Currency $currency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        HelperData $helper, Currency $currency,
        \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
        $this->currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
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

        if ( $this->getSource()->getShippingAmount()) {
            if (!$this->getSource()->getShippingCgstAmount() || $this->getSource()->getShippingCgstAmount() <= 0) {
                return $this;
            }

            $shippingCgstAmount = $this->getSource()->getShippingCgstAmount();
            $baseShippingCgstAmount = $this->getSource()->getShippingCgstAmount() / $order->getBaseToOrderRate();


            $total = new DataObject(['code' => 'shipping_cgst_amount',
                'value' => $shippingCgstAmount,
                'base_value' => $baseShippingCgstAmount,
                'label' => 'Shipping CGST']);

            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
