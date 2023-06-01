<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\IndianGst\Helper\Data;

class SaveGstToCreditmemo implements ObserverInterface
{
    protected $helper;
    protected $productFactory;
    protected $categoryRepository;
    protected $storeManager;
    protected $quoteFactory;

    public function __construct(
        Data $helper,
        ProductFactory $productFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
    }

    public function execute(Observer $observer)
    {
        $totalCgstAmount = 0;
        $totalBasecgstAmount = 0;
        $totalUtgstAmount = 0;
        $totalBaseutgstAmount = 0;
        $totalSgstAmount = 0;
        $totalBasesgstAmount = 0;
        $totalIgstAmount = 0;
        $totalBaseigstAmount = 0;
        try {
            if (!$this->helper->isEnabled()) {
                return false;
            }

            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $observer->getEvent()->getCreditmemo()->getOrder();

            $creditmemoItems = $creditmemo->getAllItems();

            /* Copy GST Data Order to Invoice */

            foreach ($creditmemoItems as $item) {
                $orderItem = $item->getOrderItem();
                $orderItemQty = $orderItem->getQtyOrdered();
                if ($item->getOrderItem()->isDummy() || $item->getQty() <= 0) {
                    continue;
                }
                $cgstAmount = ($orderItem->getCgstAmount() / $orderItemQty) * $item->getQty();
                $baseCgstAmount = ($orderItem->getBaseCgstAmount() / $orderItemQty) * $item->getQty();
                $sgstAmount = ($orderItem->getSgstAmount() / $orderItemQty) * $item->getQty();
                $baseSgstAmount = ($orderItem->getBaseSgstAmount() / $orderItemQty) * $item->getQty();
                $igstAmount = ($orderItem->getIgstAmount() / $orderItemQty) * $item->getQty();
                $baseIgstAmount = ($orderItem->getBaseIgstAmount() / $orderItemQty) * $item->getQty();
                $utgstAmount = ($orderItem->getUtgstAmount() / $orderItemQty) * $item->getQty();
                $baseUtgstAmount = ($orderItem->getBaseUtgstAmount() / $orderItemQty) * $item->getQty();

                $item->setCgstAmount($cgstAmount);
                $item->setBaseCgstAmount($baseCgstAmount);
                $item->setSgstAmount($sgstAmount);
                $item->setBaseSgstAmount($baseSgstAmount);
                $item->setIgstAmount($igstAmount);
                $item->setBaseIgstAmount($baseIgstAmount);
                $item->setUtgstAmount($utgstAmount);
                $item->setBaseUtgstAmount($baseUtgstAmount);

                $item->setIgstPercent($orderItem->getIgstPercent());
                $item->setCgstPercent($orderItem->getCgstPercent());
                $item->setUtgstPercent($orderItem->getUtgstPercent());
                $item->setSgstPercent($orderItem->getSgstPercent());

                $totalCgstAmount += $cgstAmount;
                $totalBasecgstAmount += $baseCgstAmount;
                $totalSgstAmount += $sgstAmount;
                $totalBasesgstAmount += $baseSgstAmount;
                $totalIgstAmount += $igstAmount;
                $totalBaseigstAmount += $baseIgstAmount;
                $totalUtgstAmount += $utgstAmount;
                $totalBaseutgstAmount += $baseUtgstAmount;

                $item->setGstExclusive($orderItem->getGstExclusive());

                $item->save();
            }

            $creditmemo->setCgstAmount($totalCgstAmount);
            $creditmemo->setBaseCgstAmount($totalBasecgstAmount);
            $creditmemo->setSgstAmount($totalSgstAmount);
            $creditmemo->setBaseSgstAmount($totalBasesgstAmount);
            $creditmemo->setIgstAmount($totalIgstAmount);
            $creditmemo->setBaseIgstAmount($totalBaseigstAmount);
            $creditmemo->setUtgstAmount($totalUtgstAmount);
            $creditmemo->setBaseUtgstAmount($totalBaseutgstAmount);

            $creditmemo->setSubtotalInclTax($order->getSubtotalInclTax());
            $creditmemo->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax());

            /* Copy Shipping GST Data Order to Invoice */
            if (!$creditmemo->getOrder()->getShippingInvoiced()) {
                $creditmemo->setShippingCgstAmount($order->getShippingCgstAmount());
                $creditmemo->setBaseShippingCgstAmount($order->getBaseShippingCgstAmount());

                $creditmemo->setShippingUtgstAmount($order->getShippingUtgstAmount());
                $creditmemo->setBaseShippingUtgstAmount($order->getBaseShippingUtgstAmount());

                $creditmemo->setShippingUtgstAmount($order->getShippingUtgstAmount());
                $creditmemo->setBaseShippingUtgstAmount($order->getBaseShippingUtgstAmount());

                $creditmemo->setShippingSgstAmount($order->getShippingSgstAmount());
                $creditmemo->setBaseShippingSgstAmount($order->getBaseShippingSgstAmount());

                $creditmemo->setShippingIgstAmount($order->getShippingIgstAmount());
                $creditmemo->setBaseShippingIgstAmount($order->getBaseShippingIgstAmount());
            }
            $creditmemo->setBuyerGstNumber($order->getBuyerGstNumber());
            $creditmemo->save();
        } catch (\Exception $e) {

        }
    }
}
