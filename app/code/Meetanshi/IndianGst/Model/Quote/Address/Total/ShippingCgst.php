<?php

namespace Meetanshi\IndianGst\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class ShippingCgst extends AbstractTotal
{
    protected $checkoutSession;
    protected $priceCurrency;
    protected $helper;
    protected $coreSession;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CheckoutSession $checkoutSession,
        Data $helper,
        SessionManagerInterface $coreSession
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->coreSession = $coreSession;
    }

    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $total->setTotalAmount('shipping_cgst_amount', 0);
        $total->setBaseTotalAmount('shipping_cgst_amount', 0);
        $total->setShippingCgstAmount(0);
        $total->setBaseShippingCgstAmount(0);


        $enabled = $this->helper->isEnabled();
        $isShipping = $this->helper->isShippingGst();

        $address=$this->helper->getGstAddress($shippingAssignment,$quote);
        $shippingAddress=$shippingAssignment->getShipping()->getAddress();
        if ($address && $this->helper->canApplyCgstShipping($address)) {

                if ($enabled && $isShipping && $shippingAddress->getShippingAmount()) {
                $this->coreSession->setShippingGstAmount($shippingAddress->getShippingAmount());
                $shippingGst = $this->helper->calculateCgstSgstShipping(
                    $shippingAddress->getShippingAmount(),
                    $quote->getEntityId(),
                    $shippingAssignment
                );

                $total->setShippingCgstAmount($shippingGst);
                $total->setBaseShippingCgstAmount($shippingGst);
                $address->setShippingCgstAmount($shippingGst);
                $address->setBaseShippingCgstAmount($shippingGst);
                $address->setTotalAmount('shipping_cgst_amount', $shippingGst);
                $address->setBaseTotalAmount('shipping_cgst_amount', $shippingGst);

                if ($this->helper->getShippingGstClass()) {
                    $total->setTotalAmount('shipping_cgst_amount', $shippingGst);
                    $total->setBaseTotalAmount('shipping_cgst_amount', $shippingGst);
                    $total->setGrandTotal($total->getGrandTotal() + $shippingGst);
                    $total->setBaseGrandTotal($total->getBaseGrandTotal() + $shippingGst);
                } else {
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                }
                $quote->setShippingCgstAmount($shippingGst);
                $quote->setBaseShippingCgstAmount($shippingGst);
            }
        }
        else {
            $total->setShippingCgstAmount(0);
            $total->setBaseShippingCgstAmount(0);
            $quote->setShippingCgstAmount(0);
            $quote->setBaseShippingCgstAmount(0);
        }

        return $this;
    }

    public function fetch(Quote $quote, Quote\Address\Total $total)
    {
        $enabled = $this->helper->isEnabled();
        $isShipping = $this->helper->isShippingGst();
        $result = [];
        $cgstAmount = $total->getShippingCgstAmount();

        if ($enabled && $cgstAmount && $isShipping && $cgstAmount > 0) {
            $result = [
                'code' => 'shipping_cgst_amount',
                'title' => __('Shipping CGST'),
                'value' => $cgstAmount
            ];
        }

        return $result;
    }

    public function getLabel()
    {
        return __('Shipping CGST');
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
