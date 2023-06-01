<?php

namespace Meetanshi\IndianGst\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class Igst extends AbstractTotal
{
    protected $checkoutSession;
    protected $priceCurrency;
    protected $helper;

    public function __construct(PriceCurrencyInterface $priceCurrency, CheckoutSession $checkoutSession, Data $helper)
    {
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $total->setTotalAmount('igst_amount', 0);
        $total->setBaseTotalAmount('igst_amount', 0);
        $total->setIgstAmount(0);
        $total->setBaseIgstAmount(0);

        $enabled = $this->helper->isEnabled();
        $address=$this->helper->getGstAddress($shippingAssignment,$quote);

        if ($address && $this->helper->canApplyIgst($address)) {
            $igstAmount = $this->helper->calculateIgst($quote->getEntityId(), $shippingAssignment);
            if ($enabled) {
                $total->setIgstAmount($igstAmount);
                $total->setBaseIgstAmount($igstAmount);
                $quote->setIgstAmount($igstAmount);
                $quote->setBaseIgstAmount($igstAmount);

                if ($this->helper->isCatalogExclusiveGst()) {
                    $total->setTotalAmount('igst_amount', $igstAmount);
                    $total->setBaseTotalAmount('igst_amount', $igstAmount);
                } else {
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                }
            }
        } else {
            $total->setIgstAmount(0);
            $total->setBaseIgstAmount(0);
            $quote->setIgstAmount(0);
            $quote->setBaseIgstAmount(0);
        }


        return $this;
    }

    public function fetch(Quote $quote, Quote\Address\Total $total)
    {
        $enabled = $this->helper->isEnabled();
        $result = [];

        $igstAmount = $total->getIgstAmount();
        if ($enabled && $igstAmount) {
            $result = ['code' => 'igst_amount', 'title' => __('IGST'), 'value' => $igstAmount];
        }
        return $result;
    }

    public function getLabel()
    {
        return __('IGST');
    }

    protected function clearValues(Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
}
