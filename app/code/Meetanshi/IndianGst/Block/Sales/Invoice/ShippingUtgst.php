<?php

namespace Meetanshi\IndianGst\Block\Sales\Invoice;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Invoice\Totals;
use Meetanshi\IndianGst\Helper\Data as HelperData;

/**
 * Class ShippingUtgst
 * @package Meetanshi\IndianGst\Block\Sales\Order
 */
class ShippingUtgst extends Totals
{
    /**
     * @var HelperData
     */
    protected $helper;
    /**
     * @var Currency
     */
    protected $currency;


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
        $this->getSource();

        if ( $this->getSource()->getShippingAmount()) {
            if (!$this->getSource()->getShippingUtgstAmount() || $this->getSource()->getShippingUtgstAmount() <= 0) {
                return $this;
            }

            $shippingUtgstAmount = $this->getSource()->getShippingUtgstAmount();
            $baseShippingUtgstAmount = $this->getSource()->getShippingUtgstAmount() / $order->getBaseToOrderRate();

            $total = new DataObject(['code' => 'shipping_utgst_amount',
                'value' => $shippingUtgstAmount,
                'base_value' => $baseShippingUtgstAmount,
                'label' => 'Shipping UTGST']);

            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
