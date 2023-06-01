<?php

namespace Meetanshi\IndianGst\Block\Sales\Creditmemo;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Creditmemo\Totals;
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

        if ($this->getCreditmemo()->getShippingSgstAmount() > 0) {
            $total = new DataObject(['code' => 'shipping_sgst_amount',
                'value' => $this->getCreditmemo()->getShippingSgstAmount(),
                'base_value' => $this->getCreditmemo()->getBaseShippingSgstAmount(),
                'label' => 'Shipping SGST']);
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
