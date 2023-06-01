<?php

namespace Meetanshi\IndianGst\Block\Sales\Creditmemo;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Creditmemo\Totals;
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
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
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
        if ($this->getCreditmemo()->getShippingCgstAmount() > 0) {
            $total = new DataObject(['code' => 'shipping_cgst_amount',
                'value' => $this->getCreditmemo()->getShippingCgstAmount(),
                'base_value' => $this->getCreditmemo()->getBaseShippingCgstAmount(),
                'label' => 'Shipping CGST']);
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
