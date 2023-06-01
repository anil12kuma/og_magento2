<?php

namespace Meetanshi\IndianGst\Model\Quote\Address\Total;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Area;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class Utgst extends AbstractTotal
{
    protected $checkoutSession;
    protected $priceCurrency;
    protected $helper;
    private $isUnionTerritory = false;

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

        $total->setTotalAmount('utgst_amount', 0);
        $total->setBaseTotalAmount('utgst_amount', 0);
        $total->setUtgstAmount(0);
        $total->setBaseUtgstAmount(0);

        $enabled = $this->helper->isEnabled();
        $address=$this->helper->getGstAddress($shippingAssignment,$quote);


        if ($address && Area::AREA_ADMINHTML) {
            $shipOrigin = $address->getRegionId();
            $mainOrigin = $this->helper->getOrigin();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            if ($shipOrigin == $mainOrigin) {
                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSame("SAME");
                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSSame("SSAME");
            } elseif ($address->getCountryId() == 'IN' && (!empty($address->getRegionCode()))) {
                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSame("DIFF");
                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSSame("SDIFF");
            } else {
                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSame("N");

                $objectManager->get(
                    'Magento\Customer\Model\Session'
                )->setIsSSame("SN");

                $total->setCgstAmount(0);
                $total->setBaseCgstAmount(0);
                $total->setUtgstAmount(0);
                $total->setBaseUtgstAmount(0);
                $total->setSgstAmount(0);
                $total->setBaseSgstAmount(0);
                $total->setIgstAmount(0);
                $total->setBaseIgstAmount(0);
            }
        }

        if ($address && $this->helper->canApplyUtgst($address)) {
            $utgstAmount = $this->helper->calculateUtgst($quote->getEntityId(), $shippingAssignment);
            if ($enabled) {
                $total->setUtgstAmount($utgstAmount);
                $total->setBaseUtgstAmount($utgstAmount);
                $quote->setUtgstAmount($utgstAmount);
                $quote->setBaseUtgstAmount($utgstAmount);
                if ($this->helper->isCatalogExclusiveGst()) {
                    $total->setTotalAmount('utgst_amount', $utgstAmount);
                    $total->setBaseTotalAmount('utgst_amount', $utgstAmount);
                } else {
                    $total->setBaseGrandTotal($total->getBaseGrandTotal());
                }
            }
        }

        return $this;
    }

    public function fetch(Quote $quote, Quote\Address\Total $total)
    {
        $enabled = $this->helper->isEnabled();
        $result = [];

        $utgstAmount = $total->getUtgstAmount();

        if ($enabled && $utgstAmount) {
            $result = ['code' => 'utgst_amount', 'title' => __('UTGST'), 'value' => $utgstAmount];
        }

        return $result;
    }

    public function getLabel()
    {
        return __('UTGST');
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
