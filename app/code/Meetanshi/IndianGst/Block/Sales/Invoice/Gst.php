<?php

namespace Meetanshi\IndianGst\Block\Sales\Invoice;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Invoice\Totals;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Directory\Model\Currency;

/**
 * Class Gst
 * @package Meetanshi\IndianGst\Block\Sales\Invoice
 */
class Gst extends Totals
{
    /**
     * @var HelperData
     */
    protected $helper;
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * Gst constructor.
     * @param Context $context
     * @param HelperData $helper
     * @param Currency $currency
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        HelperData $helper,
        Currency $currency,
        \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
        $this->currency = $currency;
    }

    /**
     * Get label cell tag properties
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getInvoice();
    }


    /**
     * Get value cell tag properties
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $order = $this->getOrder();
        $source = $this->getSource();
        if (!$source) {
            $invoiceid = $this->_request->getParam('invoice_id');
            if (!$invoiceid)
                $invoiceid = $order->getInvoiceCollection()->getLastItem()->getData('entity_id');
            $invoices = $order->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                if ($invoice->getEntityId() == $invoiceid)
                    $source = $invoice;
            }
        }

        if ((double)$this->getOrder()->getSubtotal()) {
            $value = $source->getSubtotal();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'subtotal',
                        'strong' => false,
                        'label' => 'Subtotal',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
            );
        }
         if ((double)$this->getOrder()->getCgstAmount()) {
            $value = $source->getCgstAmount();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'cgst_amount',
                        'strong' => false,
                        'label' => 'CGST',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
            );
        }

        if ((double)$this->getOrder()->getUtgstAmount()) {
            $value = $source->getUtgstAmount();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'utgst_amount',
                        'strong' => false,
                        'label' => 'UTGST',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
            );
        }

        if ((double)$this->getOrder()->getSgstAmount()) {
            $value = $source->getSgstAmount();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'sgst_amount',
                        'strong' => false,
                        'label' => 'SGST',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
            );
        }

        if ((double)$this->getOrder()->getIgstAmount()) {
            $value = $source->getIgstAmount();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'igst_amount',
                        'strong' => false,
                        'label' => 'IGST',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
            );
        }

        if ((double)$this->getOrder()->getGrandTotal()) {
            $value = $source->getGrandTotal();
            $baseValue = $value / $order->getBaseToOrderRate();

            $this->getParentBlock()->addTotal(
                new \Magento\Framework\DataObject(
                    [
                        'code' => 'grand_total',
                        'strong' => true,
                        'label' => 'Grand Total',
                        'value' => $value,
                        'base_value' => $baseValue,
                    ]
                )
                , "last"
            );
        }


        return $this;
    }
}
