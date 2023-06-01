<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class AddGstToPaypal implements ObserverInterface
{

    /**
     * @var Data
     */
    private $helper;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Data $helper,
        ProductFactory $productFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(EventObserver $observer)
    {
        try {
            if (!$this->helper->isEnabled()) {
                return $this;
            }

            $totals = 0;
            $quote = $this->checkoutSession->getQuote();
            $quoteId = $quote->getEntityId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $quotedItems = $quote->getAllVisibleItems();
            $excludingGst = $this->helper->isCatalogExclusiveGst();

            $cart = $observer->getEvent()->getCart();
            if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                $gstAddress = $quote->getShippingAddress();
            } else {
                $gstAddress = $quote->getBillingAddress();
            }

            if ($gstAddress->getCountryId() == "IN") {
                $isUnionTarritotial = $this->helper->getCheckUnionTerritory($gstAddress->getRegion());
                foreach ($quotedItems as $item) {
                    if ($item->getQuoteItemId()) {
                        $product = $this->productFactory->create()->load($item->getData('product_id'));
                        $categoryIds = $product->getCategoryIds();
                        $productPriceAfterDiscount = ($product->getFinalPrice() * $product->getDiscountPercent()) / 100;
                        $productPrice = $product->getFinalPrice() - $productPriceAfterDiscount;
                        $flag = false;
                        if (sizeof($categoryIds) > 0) {
                            foreach ($categoryIds as $categoryId) {
                                $category = $this->categoryRepository->get($categoryId,
                                    $this->storeManager->getStore()->getId());
                                $catGstRate = $category->getCatGstRate();
                                $gstRateMinAmount = $category->getCatMinGstAmount();
                                $gstMinRate = $category->getCatMinGstRate();
                                if ($catGstRate >= 0 && $catGstRate !== null) {
                                    if ($category->getCatGstRate()) {
                                        $flag = true;
                                    }
                                    break;
                                }
                            }
                        }
                        if ($product->getGstRate() >= 0 && $product->getGstRate() !== null) {
                            $gstRate = $product->getGstRate();
                            $gstMinimumPrice = $product->getMinGstAmount();
                            if ($gstMinimumPrice > $productPrice) {
                                $gstRate = $product->getMinGstRate();
                            }
                            if ($this->helper->isCatalogExclusiveGst()) {
                                $gstAmount = ((($productPrice) * $gstRate) / 100);
                            } else {
                                $gstPercent = 100 + $gstRate;
                                $productPrice = ($productPrice) / $gstPercent;
                                $gstAmount = $productPrice * $gstRate;
                            }
                            $sub = $item->getData('qty_ordered') * $gstAmount;
                            if ($this->helper->canApplyIgst($gstAddress)) {
                                $item->setIgstAmount($sub);
                                $item->setBaseIgstAmount($sub);
                                $item->setIgstPercent($gstRate);
                            } else {
                                $item->setCgstAmount($sub / 2);
                                $item->setBaseCgstAmount($sub / 2);
                                $item->setCgstPercent($gstRate / 2);
                                if ($isUnionTarritotial) {
                                    $item->setUtgstAmount($sub / 2);
                                    $item->setBaseUtgstAmount($sub / 2);
                                    $item->setUtgstPercent($gstRate / 2);
                                } else {
                                    $item->setSgstAmount($sub / 2);
                                    $item->setBaseSgstAmount($sub / 2);
                                    $item->setSgstPercent($gstRate / 2);
                                }
                            }
                            $totals = $totals + $sub;
                        } elseif ($flag) {
                            if ($gstRateMinAmount > $productPrice) {
                                $catGstRate = $gstMinRate;
                            }
                            if ($this->helper->isCatalogExclusiveGst()) {
                                $gstAmount = ((($productPrice) * $catGstRate) / 100);
                            } else {
                                $gstPercent = 100 + $catGstRate;
                                $productPrice = ($productPrice) / $gstPercent;
                                $gstAmount = $productPrice * $catGstRate;
                            }
                            $sub = $item->getData('qty_ordered') * $gstAmount;
                            if ($this->helper->canApplyIgst($gstAddress)) {
                                $item->setIgstAmount($sub);
                                $item->setBaseIgstAmount($sub);
                                $item->setIgstPercent($catGstRate);
                            } else {
                                $item->setCgstAmount($sub / 2);
                                $item->setBaseCgstAmount($sub / 2);
                                $item->setCgstPercent($catGstRate / 2);
                                if ($isUnionTarritotial) {
                                    $item->setUtgstAmount($sub / 2);
                                    $item->setBaseUtgstAmount($sub / 2);
                                    $item->setUtgstPercent($catGstRate / 2);
                                } else {
                                    $item->setSgstAmount($sub / 2);
                                    $item->setBaseSgstAmount($sub / 2);
                                    $item->setSgstPercent($catGstRate / 2);
                                }
                            }
                            $totals = $totals + $sub;
                        } else {
                            $rate = $this->helper->getRate();
                            if ($this->helper->getMinAmount() > $productPrice) {
                                $rate = $this->helper->getMinRate();
                            }
                            if ($this->helper->isCatalogExclusiveGst()) {
                                $gstAmount = ((($productPrice) * $rate) / 100);
                            } else {
                                $gstPercent = 100 + $rate;
                                $productPrice = ($productPrice) / $gstPercent;
                                $gstAmount = $productPrice * $rate;
                            }
                            $sub = $item->getData('qty_ordered') * $gstAmount;
                            if ($this->helper->canApplyIgst($gstAddress)) {
                                $item->setIgstAmount($sub);
                                $item->setBaseIgstAmount($sub);
                                $item->setIgstPercent($rate);
                            } else {
                                $item->setCgstAmount($sub / 2);
                                $item->setBaseCgstAmount($sub / 2);
                                $item->setCgstPercent($rate / 2);
                                if ($isUnionTarritotial) {
                                    $item->setUtgstAmount($sub / 2);
                                    $item->setBaseUtgstAmount($sub / 2);
                                    $item->setUtgstPercent($rate / 2);
                                } else {
                                    $item->setSgstAmount($sub / 2);
                                    $item->setBaseSgstAmount($sub / 2);
                                    $item->setSgstPercent($rate / 2);
                                }
                            }
                            $totals = $totals + ($sub / 2);
                        }
                        $item->setGstExclusive($excludingGst);
                    }
                }
                if ($excludingGst) {
                    if ($this->helper->canApplyIgst($gstAddress)) {
                        $cart->addCustomItem('IGST', 1, $quote->getIgstAmount());
                    } else {
                        $cart->addCustomItem('CGST', 1, $quote->getCgstAmount());
                        if ($isUnionTarritotial) {
                            $cart->addCustomItem('UTGST', 1, $quote->getUtgstAmount());
                        } else {
                            $cart->addCustomItem('SGST', 1, $quote->getSgstAmount());
                        }
                    }
                }
                if ($this->helper->isShippingGst() && $this->helper->getShippingGstClass()) {
                    if ($this->helper->canApplyCgstShipping($gstAddress)) {
                        $cart->addCustomItem('SHIPPING_CGST', 1, $quote->getShippingCgstAmount());
                        if ($isUnionTarritotial) {
                            $cart->addCustomItem('SHIPPING_UTGST', 1, $quote->getShippingUtgstAmount());
                        } else {
                            $cart->addCustomItem('SHIPPING_SGST', 1, $quote->getShippingSgstAmount());
                        }
                    } else {
                        $cart->addCustomItem('SHIPPING_IGST', 1, $quote->getShippingIgstAmount());
                    }
                }
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this;
    }
}
