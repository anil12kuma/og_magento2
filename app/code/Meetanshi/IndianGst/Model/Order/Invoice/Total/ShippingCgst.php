<?php
namespace Meetanshi\IndianGst\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal as InvoiceAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;

class ShippingCgst extends InvoiceAbstractTotal
{
    protected $helper;

    public function __construct(HelperData $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($data);
    }

    public function collect(Invoice $invoice)
    {
        $invoice->getShippingCgstAmount(0);
        if (!$invoice->getOrder()->getShippingInvoiced()) {
            $amount = $invoice->getOrder()->getShippingCgstAmount();
            $invoice->setShippingCgstAmount($amount);
        }

        if ($this->helper->getShippingGstClass()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getShippingCgstAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getShippingCgstAmount());
        } else {
            $invoice->setGrandTotal($invoice->getGrandTotal());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal());
        }
        return $this;
    }
}
