<?php

namespace Meetanshi\IndianGst\Block\Sales\Creditmemo;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Creditmemo\Totals;
use Meetanshi\IndianGst\Helper\Data as HelperData;

/**
 * Class ShippingIgst
 * @package Meetanshi\IndianGst\Block\Sales\Order
 */
class ShippingIgst extends Totals
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
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {

        if ($this->getCreditmemo()->getShippingIgstAmount() > 0) {
            $total = new DataObject(['code' => 'shipping_igst_amount',
                'value' => $this->getCreditmemo()->getShippingIgstAmount(),
                'base_value' => $this->getCreditmemo()->getBaseShippingIgstAmount(),
                'label' => 'Shipping IGST']);
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
