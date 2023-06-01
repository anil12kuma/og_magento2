<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Meetanshi\IndianGst\Helper\Data;

class ShowBuyerGst implements ObserverInterface
{
    protected $template;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper,Template $template)
    {
        $this->template = $template;
        $this->helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        if ($observer->getElementName() == 'order_shipping_view') {
            $orderShippingView = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingView->getOrder();
            $buyerGst = __('N/A');
            $gstAddress = $order->getShippingAddress();
            if ($gstAddress == null) {
                $gstAddress = $order->getBillingAddress();
            }
            if ($gstAddress->getBuyerGstNumber() != '') {
                $buyerGst = $gstAddress->getBuyerGstNumber();
            }
            if ($order->getBuyerGstNumber() != '') {
                $buyerGst = $order->getBuyerGstNumber();
            }

            $this->template->setBuyerGstNumber($buyerGst);
            $html = $observer->getTransport()->getOutput() . $this->template->setTemplate('Meetanshi_IndianGst::buyergst.phtml')->toHtml();
            $observer->getTransport()->setOutput($html);
        }
        if ($observer->getElementName() == 'sales_invoice_view') {
            $orderInvoiceView = $observer->getLayout()->getBlock($observer->getElementName());
            $invoice = $orderInvoiceView->getInvoice();

            $buyerGst = __('N/A');
            $gstAddress = $invoice->getShippingAddress();
            if ($gstAddress == null) {
                $gstAddress = $invoice->getBillingAddress();
            }
            if ($gstAddress->getBuyerGstNumber() != '') {
                $buyerGst = $gstAddress->getBuyerGstNumber();
            }
            if ($invoice->getOrder()->getBuyerGstNumber() != '') {
                $buyerGst = $invoice->getOrder()->getBuyerGstNumber();
            }
            $this->template->setBuyerGstNumber($buyerGst);

            $html = $observer->getTransport()->getOutput() . $this->template->setTemplate('Meetanshi_IndianGst::buyergst.phtml')->toHtml();
            $observer->getTransport()->setOutput($html);
        }
        if ($observer->getElementName() == 'sales_creditmemo_view') {
            $orderCreditmemoView = $observer->getLayout()->getBlock($observer->getElementName());
            $creditmemo = $orderCreditmemoView->getCreditmemo();
            $buyerGst = __('N/A');
            $gstAddress = $creditmemo->getShippingAddress();
            if ($gstAddress == null) {
                $gstAddress = $creditmemo->getBillingAddress();
            }
            if ($gstAddress->getBuyerGstNumber() != '') {
                $buyerGst = $creditmemo->getBuyerGstNumber();
            }
            if ($creditmemo->getOrder()->getBuyerGstNumber() != '') {
                $buyerGst = $creditmemo->getOrder()->getBuyerGstNumber();
            }
            $this->template->setBuyerGstNumber($buyerGst);

            $html = $observer->getTransport()->getOutput() . $this->template->setTemplate('Meetanshi_IndianGst::buyergst.phtml')->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}
