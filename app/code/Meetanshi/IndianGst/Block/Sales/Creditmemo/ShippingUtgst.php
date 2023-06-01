<?php

namespace Meetanshi\IndianGst\Block\Sales\Creditmemo;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Creditmemo\Totals;
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

        if ($this->getCreditmemo()->getShippingUtgstAmount() > 0) {
            $total = new DataObject(['code' => 'shipping_utgst_amount',
                'value' => $this->getCreditmemo()->getShippingUtgstAmount(),
                'base_value' => $this->getCreditmemo()->getBaseShippingUtgstAmount(),
                'label' => 'Shipping UTGST']);
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }
}
