<?php

namespace Meetanshi\IndianGst\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal as CreditmemoAbstractTotal;
use Meetanshi\IndianGst\Helper\Data as HelperData;

class AbstractTotal extends CreditmemoAbstractTotal
{
    protected $helper;

    public function __construct(HelperData $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($data);
    }

    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
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
        $order = $creditmemo->getOrder();

        foreach ($creditmemo->getAllItems() as $item) {
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

        $creditmemo->setCgstAmount($totalCgstAmount);
        $creditmemo->setBaseCgstAmount($totalBasecgstAmount);
        $creditmemo->setSgstAmount($totalSgstAmount);
        $creditmemo->setBaseSgstAmount($totalBasesgstAmount);
        $creditmemo->setIgstAmount($totalIgstAmount);
        $creditmemo->setBaseIgstAmount($totalBaseigstAmount);
        $creditmemo->setUtgstAmount($totalUtgstAmount);
        $creditmemo->setBaseUtgstAmount($totalBaseutgstAmount);

        $creditmemo->setSubtotalInclTax($subtotalInclTax);
        $creditmemo->setBaseSubtotalInclTax($baseSubtotalInclTax);

        if ($this->helper->isCatalogExclusiveGst()) {
            $creditmemo->setSubtotal($subtotal);
            $creditmemo->setBaseSubtotal($baseSubtotal);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $subtotal + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseSubtotal + $baseAmount);
        } else {
            $creditmemo->setSubtotal($subtotal - $amount);
            $creditmemo->setBaseSubtotal($baseSubtotal - $baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $subtotalInclTax);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseSubtotalInclTax);
        }

        return $this;
    }
}
