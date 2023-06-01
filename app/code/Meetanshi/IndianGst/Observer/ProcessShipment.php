<?php
/**
 * Provider: Meetanshi.
 * Package: Meetanshi_Instagram
 * Support: support@meetanshi.com (https://meetanshi.com/)
 */

namespace Meetanshi\IndianGst\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class ProcessShipment implements ObserverInterface
{
    protected $checkoutSession;
    protected $total;
    protected $helper;
    protected $coreSession;
    protected $scopeConfig;
    protected $resourceConfig;

    public function __construct(CheckoutSession $checkoutSession, AddressTotal $total, Data $helper, SessionManagerInterface $coreSession, ScopeConfigInterface $scopeConfig, ResourceConfig $resourceConfig)
    {
        $this->checkoutSession = $checkoutSession;
        $this->total = $total;
        $this->helper = $helper;
        $this->coreSession = $coreSession;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
    }

    public function execute(EventObserver $observer)
    {
        try {
            if (!$this->helper->isEnabled()) {
                return $this;
            }
            $event = $observer->getEvent();
            $quote = $event->getQuote();
            if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                $address = $quote->getShippingAddress();
            } else {
                $address = $quote->getBillingAddress();
            }
            $shippingAddress = $quote->getShippingAddress();
            $this->coreSession->setShippingGstAmount($shippingAddress->getShippingAmount());
            $enabled = $this->helper->isEnabled();
            $isShipping = $this->helper->isShippingGst();
            if ($this->helper->canApplyShipping('SDIFF')) {
                $shippingGst = $this->helper->calculateIgstShipping($shippingAddress->getShippingAmount(), $quote->getEntityId());
                if ($enabled && $isShipping && $shippingAddress->getShippingAmount()) {
                    $this->total->setTotalAmount('shipping_igst_amount', 0);
                    $this->total->setBaseTotalAmount('shipping_igst_amount', 0);
                    $this->total->setShippingIgstAmount($shippingGst);
                    $this->total->setBaseShippingIgstAmount($shippingGst);
                    $address->setShippingIgstAmount($shippingGst);
                    $address->setBaseShippingIgstAmount($shippingGst);
                    $address->setTotalAmount('shipping_igst_amount', $shippingGst);
                    $address->setBaseTotalAmount('shipping_igst_amount', $shippingGst);
                    if ($this->helper->getShippingGstClass()) {
                        $this->total->setTotalAmount('shipping_igst_amount', $shippingGst);
                        $this->total->setBaseTotalAmount('shipping_igst_amount', $shippingGst);
                    //$this->total->setBaseGrandTotal($this->total->getBaseGrandTotal() + $shippingGst);
                        //$quote->setGrandTotal($quote->getGrandTotal() + $shippingGst);
                    } else {
                        $this->total->setBaseGrandTotal($this->total->getBaseGrandTotal());
                    }
                    $quote->setShippingIgstAmount($shippingGst);
                    $quote->setBaseShippingIgstAmount($shippingGst);
                    $quote->save();
                }
            } elseif ($this->helper->canApplyShipping('SSAME')) {
                $shippingGst = $this->helper->calculateCgstSgstShipping($shippingAddress->getShippingAmount(), $quote->getEntityId());
                if ($enabled && $isShipping && $shippingAddress->getShippingAmount()) {
                    $this->total->setShippingCgstAmount($shippingGst);
                    $this->total->setBaseShippingCgstAmount($shippingGst);
                    $address->setShippingCgstAmount($shippingGst);
                    $address->setBaseShippingCgstAmount($shippingGst);
                    $address->setTotalAmount('shipping_cgst_amount', $shippingGst);
                    $address->setBaseTotalAmount('shipping_cgst_amount', $shippingGst);
                    if ($this->helper->getShippingGstClass()) {
                        $this->total->setTotalAmount('shipping_cgst_amount', $shippingGst);
                        $this->total->setBaseTotalAmount('shipping_cgst_amount', $shippingGst);
                        $this->total->setBaseGrandTotal($this->total->getBaseGrandTotal() + $shippingGst);
                    } else {
                        $this->total->setBaseGrandTotal($this->total->getBaseGrandTotal());
                    }
                    $quote->setShippingCgstAmount($shippingGst);
                    $quote->setBaseShippingCgstAmount($shippingGst);
                    $this->total->setTotalAmount('shipping_sgst_amount', 0);
                    $this->total->setBaseTotalAmount('shipping_sgst_amount', 0);
                    $this->total->setShippingSgstAmount($shippingGst);
                    $this->total->setBaseShippingSgstAmount($shippingGst);
                    $address->setShippingSgstAmount($shippingGst);
                    $address->setBaseShippingSgstAmount($shippingGst);
                    $address->setTotalAmount('shipping_sgst_amount', $shippingGst);
                    $address->setBaseTotalAmount('shipping_sgst_amount', $shippingGst);
                    if ($this->helper->getShippingGstClass()) {
                        $this->total->setTotalAmount('shipping_sgst_amount', $shippingGst);
                        $this->total->setBaseTotalAmount('shipping_sgst_amount', $shippingGst);
                    } else {
                        $this->total->setBaseGrandTotal($this->total->getBaseGrandTotal());
                    }

                    $quote->setShippingSgstAmount($shippingGst);
                    $quote->setBaseShippingSgstAmount($shippingGst);
                }
            }
        } catch (\Exception $e) {
        }
        return $this;
    }
}
