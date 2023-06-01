<?php
namespace Meetanshi\IndianGst\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;
use Meetanshi\IndianGst\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{
    protected $helper;
    protected $checkoutSession;
    protected $coreSession;

    public function __construct(Data $helper, CheckoutSession $checkoutSession, SessionManagerInterface $coreSession)
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->coreSession = $coreSession;
    }

    public function getConfig()
    {
        $config = [];
        $enabled = $this->helper->isEnabled();
        $quote = $this->checkoutSession->getQuote();
        if ($this->helper->calculateCgst($quote->getEntityId())) {
            $config['cgst_amount'] = $this->helper->calculateCgst($quote->getEntityId());
            $config['cgst_label'] = 'CGST';
            $config['show_hide_cgst_block'] = ($enabled && $quote->getCgstAmount()) ? true : false;
            $config['show_hide_cgst_shipblock'] = ($enabled && $quote->getCgstAmount()) ? true : false;
        }

        if ($this->helper->calculateUtgst($quote->getEntityId())) {
            $config['utgst_amount'] = $this->helper->calculateUtgst($quote->getEntityId());
            $config['utgst_label'] = 'UTGST';
            $config['show_hide_utgst_block'] = ($enabled && $quote->getUtgstAmount()) ? true : false;
            $config['show_hide_utgst_shipblock'] = ($enabled && $quote->getUtgstAmount()) ? true : false;
        }

        if ($this->helper->calculateSgst($quote->getEntityId())) {
            $config['sgst_amount'] = $this->helper->calculateSgst($quote->getEntityId());
            $config['sgst_label'] = 'SGST';
            $config['show_hide_sgst_block'] = ($enabled && $quote->getSgstAmount()) ? true : false;
            $config['show_hide_sgst_shipblock'] = ($enabled && $quote->getSgstAmount()) ? true : false;
        }

        if ($this->helper->calculateIgst($quote->getEntityId())) {
            $config['igst_amount'] = $this->helper->calculateIgst($quote->getEntityId());
            $config['igst_label'] = 'IGST';
            $config['show_hide_igst_block'] = ($enabled && $quote->getIgstAmount()) ? true : false;
            $config['show_hide_igst_shipblock'] = ($enabled && $quote->getIgstAmount()) ? true : false;
        }

        if ($this->helper->isShippingGst()):
            $config['shipping_cgst_amount'] = $this->helper->calculateCgstSgstShipping($this->coreSession->getShippingGstAmount(), $quote->getEntityId());
            $config['shipping_cgst_label'] = 'Shipping CGST';
            $config['show_hide_shipping_cgst_block'] = ($this->helper->isShippingGst()) ? true : false;
            $config['show_hide_shipping_cgst_shipblock'] = ($this->helper->isShippingGst()) ? true : false;

            $config['shipping_utgst_amount'] = $this->helper->calculateCgstUtgstShipping($this->coreSession->getShippingGstAmount(), $quote->getEntityId());
            $config['shipping_utgst_label'] = 'Shipping UTGST';
            $config['show_hide_shipping_utgst_block'] = ($this->helper->isShippingGst()) ? true : false;
            $config['show_hide_shipping_utgst_shipblock'] = ($this->helper->isShippingGst()) ? true : false;

            $config['shipping_sgst_amount'] = $this->helper->calculateCgstSgstShipping($this->coreSession->getShippingGstAmount(), $quote->getEntityId());
            $config['shipping_sgst_label'] = 'Shipping SGST';
            $config['show_hide_shipping_sgst_block'] = ($this->helper->isShippingGst()) ? true : false;
            $config['show_hide_shipping_sgst_shipblock'] = ($this->helper->isShippingGst()) ? true : false;

            $config['shipping_igst_amount'] = $this->helper->calculateIgstShipping($this->coreSession->getShippingGstAmount(), $quote->getEntityId());
            $config['shipping_igst_label'] = 'Shipping IGST';
            $config['show_hide_shipping_igst_block'] = ($this->helper->isShippingGst()) ? true : false;
            $config['show_hide_shipping_igst_shipblock'] = ($this->helper->isShippingGst()) ? true : false;
        endif;

        $config['origin'] = $this->helper->getOrigin();

        return $config;
    }
}
