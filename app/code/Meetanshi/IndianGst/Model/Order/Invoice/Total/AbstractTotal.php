<?php

namespace Meetanshi\IndianGst\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal as InvoiceAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class AbstractTotal extends InvoiceAbstractTotal
{
    protected $productFactory;
    protected $orderFactory;
    protected $helper;
    protected $categoryRepository;
    protected $storeManager;

    public function __construct(
        HelperData $helper,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $totalCgstAmount = 0;
        $totalBasecgstAmount = 0;
        $totalUtgstAmount = 0;
        $totalBaseutgstAmount = 0;
        $totalSgstAmount = 0;
        $totalBasesgstAmount = 0;
        $totalIgstAmount = 0;
        $totalBaseigstAmount = 0;
        $amount = 0;
        $baseAmount = 0;
        $subtotal = 0;
        $baseSubtotal = 0;
        $subtotalInclTax = 0;
        $baseSubtotalInclTax = 0;
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($item->getOrderItem()->isDummy() || $item->getQty() <= 0) {
                continue;
            }

            $item->calcRowTotal();
            $subtotal += $item->getRowTotal();

            $baseSubtotal += $item->getBaseRowTotal();
            $subtotalInclTax += $item->getRowTotalInclTax();
            $baseSubtotalInclTax += $item->getBaseRowTotalInclTax();

            $cgstAmount = ($orderItem->getCgstAmount() / $orderItemQty) * $item->getQty();
            $baseCgstAmount = ($orderItem->getBaseCgstAmount() / $orderItemQty) * $item->getQty();
            $sgstAmount = ($orderItem->getSgstAmount() / $orderItemQty) * $item->getQty();
            $baseSgstAmount = ($orderItem->getBaseSgstAmount() / $orderItemQty) * $item->getQty();
            $igstAmount = ($orderItem->getIgstAmount() / $orderItemQty) * $item->getQty();
            $baseIgstAmount = ($orderItem->getBaseIgstAmount() / $orderItemQty) * $item->getQty();
            $utgstAmount = ($orderItem->getUtgstAmount() / $orderItemQty) * $item->getQty();
            $baseUtgstAmount = ($orderItem->getBaseUtgstAmount() / $orderItemQty) * $item->getQty();

             $item->getCgstAmount($cgstAmount);
             $item->getBaseCgstAmount($baseCgstAmount);
             $item->getSgstAmount($sgstAmount);
             $item->getBaseSgstAmount($baseSgstAmount);
             $item->getIgstAmount($igstAmount);
             $item->getBaseIgstAmount($baseIgstAmount);
             $item->getUtgstAmount($utgstAmount);
             $item->getBaseUtgstAmount($baseUtgstAmount);


            $totalCgstAmount += $cgstAmount;
            $totalBasecgstAmount += $baseCgstAmount;
            $totalSgstAmount += $sgstAmount;
            $totalBasesgstAmount += $baseSgstAmount;
            $totalIgstAmount += $igstAmount;
            $totalBaseigstAmount += $baseIgstAmount;
            $totalUtgstAmount += $utgstAmount;
            $totalBaseutgstAmount += $baseUtgstAmount;

             $amount += $cgstAmount + $sgstAmount + $igstAmount + $utgstAmount;
             $baseAmount += $baseCgstAmount + $baseSgstAmount + $baseIgstAmount + $baseUtgstAmount;
        }
        $invoice->setCgstAmount($totalCgstAmount);
        $invoice->setBaseCgstAmount($totalBasecgstAmount);
        $invoice->setSgstAmount($totalSgstAmount);
        $invoice->setBaseSgstAmount($totalBasesgstAmount);
        $invoice->setIgstAmount($totalIgstAmount);
        $invoice->setBaseIgstAmount($totalBaseigstAmount);
        $invoice->setUtgstAmount($totalUtgstAmount);
        $invoice->setBaseUtgstAmount($totalBaseutgstAmount);

        $invoice->setSubtotalInclTax($subtotalInclTax);
        $invoice->setBaseSubtotalInclTax($baseSubtotalInclTax);

        if ($this->helper->isCatalogExclusiveGst()) {
            $invoice->setSubtotal($subtotal );
            $invoice->setBaseSubtotal($baseSubtotal);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $subtotal + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSubtotal + $baseAmount);
        } else {
            $invoice->setSubtotal($subtotal - $amount);
            $invoice->setBaseSubtotal($baseSubtotal - $baseAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $subtotalInclTax);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseSubtotalInclTax);
        }
        return $this;
    }
}
