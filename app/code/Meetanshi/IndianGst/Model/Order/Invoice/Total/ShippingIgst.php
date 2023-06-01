<?php
namespace Meetanshi\IndianGst\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal as InvoiceAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Sales\Model\Order\Invoice;

class ShippingIgst extends InvoiceAbstractTotal
{
    protected $helper;

    public function __construct(HelperData $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($data);
    }

    public function collect(Invoice $invoice)
    {

        if (!$invoice->getOrder()->getShippingInvoiced()) {
            $amount = $invoice->getOrder()->getShippingIgstAmount();
            $invoice->setShippingIgstAmount($amount);
        }

        if ($this->helper->getShippingGstClass()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getShippingIgstAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getShippingIgstAmount());
        } else {
            $invoice->setGrandTotal($invoice->getGrandTotal());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal());
        }
        return $this;
    }
}
