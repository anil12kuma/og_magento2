<?php

namespace Meetanshi\IndianGst\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session as CheckoutSession;
use Meetanshi\IndianGst\Helper\Data;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class ShippingIgst extends AbstractTotal
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

        $total->setTotalAmount('shipping_igst_amount', 0);
        $total->setBaseTotalAmount('shipping_igst_amount', 0);
        $total->setShippingIgstAmount(0);
        $total->setBaseShippingIgstAmount(0);
        $quote->setShippingIgstAmount(0);
        $quote->setBaseShippingIgstAmount(0);

        $enabled = $this->helper->isEnabled();
        $isShipping = $this->helper->isShippingGst();
        $address = $this->helper->getGstAddress($shippingAssignment, $quote);

        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        if ($address && $this->helper->canApplyIgstShipping($address)) {
            if ($enabled && $isShipping && $shippingAddress->getShippingAmount()) {
                $this->coreSession->setShippingGstAmount($shippingAddress->getShippingAmount());
                $shippingGst = $this->helper->calculateIgstShipping(
                    $shippingAddress->getShippingAmount(),
                    $quote->getEntityId(),
                    $shippingAssignment
                );

                $total->setShippingIgstAmount($shippingGst);
                $total->setBaseShippingIgstAmount($shippingGst);


                $address->setShippingIgstAmount($shippingGst);
                $address->setBaseShippingIgstAmount($shippingGst);
                $address->setTotalAmount('shipping_igst_amount', $shippingGst);
                $address->setBaseTotalAmount('shipping_igst_amount', $shippingGst);

                if ($this->helper->getShippingGstClass()) {
                    $total->setTotalAmount('shipping_igst_amount', $shippingGst);
                    $total->setBaseTotalAmount('shipping_igst_amount', $shippingGst);
                    $total->setGrandTotal($total->getGrandTotal() + $shippingGst);
                    $total->setBaseGrandTotal($total->getBaseGrandTotal() + $shippingGst);
                } else {
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                }
                $quote->setShippingIgstAmount($shippingGst);
                $quote->setBaseShippingIgstAmount($shippingGst);
            }
        } else {
            $total->setShippingIgstAmount(0);
            $total->setBaseShippingIgstAmount(0);
            $quote->setShippingIgstAmount(0);
            $quote->setBaseShippingIgstAmount(0);
        }
        if (!$this->helper->isCatalogExclusiveGst()) {
            $totalSubTotalTax = $total->getCgstAmount() + $total->getSgstAmount() + $total->getIgstAmount() + $total->getUtgstAmount();
            $totalBaseSubTotalTax = $total->getBaseCgstAmount() + $total->getBaseSgstAmount() + $total->getBaseIgstAmount() + $total->getBaseUtgstAmount();
            $quoteSubTotalTax = $quote->getCgstAmount() + $quote->getSgstAmount() + $quote->getIgstAmount() + $quote->getUtgstAmount();
            $quoteBaseSubTotalTax = $quote->getBaseCgstAmount() + $quote->getBaseSgstAmount() + $quote->getBaseIgstAmount() + $quote->getBaseUtgstAmount();

            $total->setData('subtotal', $total->getData('subtotal') - $totalSubTotalTax);
            $total->setData('base_subtotal', $total->getData('base_subtotal') - $totalBaseSubTotalTax);
            $quote->setData('subtotal', $quote->getData('subtotal') - $quoteSubTotalTax);
            $quote->setData('base_subtotal', $quote->getData('base_subtotal') - $quoteBaseSubTotalTax);
        }
        return $this;
    }

    public function fetch(Quote $quote, Quote\Address\Total $total)
    {
        $enabled = $this->helper->isEnabled();
        $isShipping = $this->helper->isShippingGst();
        $result = [];
        $shippingIgstAmount = $total->getShippingIgstAmount();
        if ($enabled && $shippingIgstAmount && $isShipping && $shippingIgstAmount > 0) {
            $result = [
                'code' => 'shipping_igst_amount',
                'title' => __('Shipping IGST'),
                'value' => $shippingIgstAmount
            ];
        }


        return $result;
    }

    public function getLabel()
    {
        return __('Shipping IGST');
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
