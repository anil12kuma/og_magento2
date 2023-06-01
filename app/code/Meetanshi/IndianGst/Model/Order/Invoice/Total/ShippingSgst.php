<?php
namespace Meetanshi\IndianGst\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal as InvoiceAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Sales\Model\Order\Invoice;

class ShippingSgst extends InvoiceAbstractTotal
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
            $amount = $invoice->getOrder()->getShippingSgstAmount();
            $invoice->setShippingSgstAmount($amount);
        }


        if ($this->helper->getShippingGstClass()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getShippingSgstAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getShippingSgstAmount());
        } else {
            $invoice->setGrandTotal($invoice->getGrandTotal());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal());
        }

        return $this;
    }
}
