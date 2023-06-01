<?php

namespace Meetanshi\IndianGst\Block\Sales\Invoice;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Invoice\Totals;
use Magento\Sales\Model\Order;
use Meetanshi\IndianGst\Helper\Data as HelperData;

/**
 * Class ShippingSgst
 * @package Meetanshi\IndianGst\Block\Sales\Order
 */
class ShippingSgst extends Totals
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

        if ( $this->getSource()->getShippingAmount()) {
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
        }
        return $this;
    }
}
