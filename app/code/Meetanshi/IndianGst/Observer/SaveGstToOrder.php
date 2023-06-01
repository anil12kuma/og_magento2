<?php

namespace Meetanshi\IndianGst\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\IndianGst\Helper\Data;
use Meetanshi\IndianGst\Model\Config\Source\ShippingBillingAddress;

class SaveGstToOrder implements ObserverInterface
{
    protected $quoteRepository;
    protected $customerFactory;
    protected $helper;
    protected $productFactory;
    protected $orderFactory;
    protected $categoryRepository;
    protected $storeManager;
    protected $quoteFactory;
    protected $orderSender;
    protected $checkoutSession;
    protected $scopeConfig;
    protected $resourceConfig;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $addressRepository;
    private $quoteItemFactory;
    private $serializer;

    public function __construct(
        Json $serializer,
        QuoteItemFactory $quoteItemFactory,
        QuoteRepository $quoteRepository,
        CustomerFactory $customerFactory,
        Data $helper,
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        OrderSender $orderSender,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        AddressRepositoryInterface $addressRepository,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->customerFactory = $customerFactory;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->addressRepository = $addressRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->serializer = $serializer;
    }

    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        if ($observer->getEvent()->getName() == 'checkout_type_multishipping_create_orders_single') {
            if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                $gstAddress = $observer->getAddress();
            } else {
                $gstAddress = $quote->getBillingAddress();
            }
            $this->saveGstToOrder($order, $gstAddress, $quote);
        } else {
            if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                $gstAddress = $quote->getShippingAddress();
            } else {
                $gstAddress = $quote->getBillingAddress();
            }
            $this->saveGstToOrder($order, $gstAddress, $quote);
        }
        return $this;
    }

    public function saveGstToOrder(Order $order, Address $gstAddress, Quote $quote)
    {
        if ($this->helper->isEnabled()) {
            if ($gstAddress->getCountryId() == "IN") {
                $isUnionTarritotial = $this->helper->getCheckUnionTerritory($gstAddress->getRegion());
                $totals = 0;
                $excludingGst = $this->helper->isCatalogExclusiveGst();
                $orderItems = $order->getAllItems();

                foreach ($orderItems as $key => $item) {
                    $itemPrice = 0;
                    if ($item->getQuoteItemId()) {
                        $product = $this->productFactory->create()->load($item->getData('product_id'));
                        if ($product->getTypeId() == 'bundle') {
                            if (!$this->helper->isCatalogExclusiveGst()) {
                                $item->setPrice(0);
                                $item->setBasePrice(0);
                            }
                            $item->setIgstAmount(0);
                            $item->setBaseIgstAmount(0);
                            $item->setCgstAmount(0);
                            $item->setBaseCgstAmount(0);
                            $item->setUtgstAmount(0);
                            $item->setBaseUtgstAmount(0);
                            $item->setSgstAmount(0);
                            $item->setBaseSgstAmount(0);
                            continue;
                        }
                        if ($product->getTypeId() == 'configurable') {
                            foreach ($item->getChildrenItems() as $childrenItem) {
                                $product = $this->productFactory->create()->load($childrenItem->getData('product_id'));
                            }
                        }
                        $categoryIds = $product->getCategoryIds();
                        $productPrice = $tmep = $item->getRowTotal();
                        $flag = false;
                        if (sizeof($categoryIds) > 0) {
                            foreach ($categoryIds as $categoryId) {
                                $category = $this->categoryRepository->get(
                                    $categoryId,
                                    $this->storeManager->getStore()->getId()
                                );
                                $catGstRate = $category->getCatGstRate();
                                $gstRateMinAmount = $category->getCatMinGstAmount();
                                $gstMinRate = $category->getCatMinGstRate();
                                if ($catGstRate >= 0 && $catGstRate !== null) {
                                    if ($category->getCatGstRate() >= 0 && $category->getCatGstRate() !== null) {
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
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ((($productPrice) * $gstRate) / 100);
                            } else {
                                $gstPercent = 100 + $gstRate;
                                $productPrice = ($productPrice / $gstPercent) * 100;
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ($productPrice * $gstRate) / 100;
                            }
                            $sub = $gstAmount;
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
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ((($productPrice) * $catGstRate) / 100);
                            } else {
                                $gstPercent = 100 + $catGstRate;
                                $productPrice = ($productPrice / $gstPercent) * 100;
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ($productPrice * $catGstRate) / 100;
                            }

                            $sub = $gstAmount;

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
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ((($productPrice) * $rate) / 100);
                            } else {
                                $gstPercent = 100 + $rate;
                                $productPrice = ($productPrice / $gstPercent) * 100;
                                $itemPrice = $productPrice;
                                $productPrice -= ($productPrice * $item->getDiscountPercent()) / 100;
                                $gstAmount = ($productPrice * $rate) / 100;
                            }
                            $sub = $gstAmount;
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
                        try {
                            if (!$this->helper->isCatalogExclusiveGst()) {
                                $item->setPrice($itemPrice / $item->getQtyOrdered()) ;
                                $item->setBasePrice($itemPrice / $item->getQtyOrdered());
                                $productOptions = $item->getProductOptions();

                                if ($item->getParentItem() && $item->getParentItem()->getProductType() == 'bundle') {
                                    $productOptions = $item->getProductOptions();
                                    if (isset($productOptions['bundle_selection_attributes'])) {
                                        $bundleOptionAttribute = $this->serializer->unserialize($productOptions['bundle_selection_attributes']);
                                        $bundleOptionAttribute['price'] = $itemPrice;
                                        $productOptions['bundle_selection_attributes'] = $this->serializer->serialize($bundleOptionAttribute);
                                    }
                                    $item->getParentItem()->setPrice($item->getParentItem()->getPrice() + ($itemPrice / $item->getQtyOrdered()));
                                    $item->getParentItem()->setBasePrice($item->getParentItem()->getBasePrice() + ($itemPrice / $item->getQtyOrdered() ));
                                }
                                $item->setProductOptions($productOptions);
                            }
                        } catch (\Exception $e) {
                            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
                        }
                        if ($item->getParentItem() && $item->getParentItem()->getProductType() == 'bundle') {
                            $item->getParentItem()->setIgstAmount($item->getParentItem()->getIgstAmount() + $item->getIgstAmount());
                            $item->getParentItem()->setBaseIgstAmount($item->getParentItem()->getBaseIgstAmount() + $item->getBaseIgstAmount());
                            $item->getParentItem()->setCgstAmount($item->getParentItem()->getCgstAmount() + $item->getCgstAmount());
                            $item->getParentItem()->setBaseCgstAmount($item->getParentItem()->getBaseCgstAmount() + $item->getBaseCgstAmount());
                            $item->getParentItem()->setUtgstAmount($item->getParentItem()->getUtgstAmount() + $item->getUtgstAmount());
                            $item->getParentItem()->setBaseUtgstAmount($item->getParentItem()->getBaseUtgstAmount() + $item->getBaseUtgstAmount());
                            $item->getParentItem()->setSgstAmount($item->getParentItem()->getSgstAmount() + $item->getSgstAmount());
                            $item->getParentItem()->setBaseSgstAmount($item->getParentItem()->getBaseSgstAmount() + $item->getBaseSgstAmount());
                        }
                    }
                }

                if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                    $igstAmount = $gstAddress->getIgstAmount();
                    $baseIgstAmount = $gstAddress->getBaseIgstAmount();
                    $cgstAmount = $gstAddress->getCgstAmount();
                    $baseCgstAmount = $gstAddress->getBaseCgstAmount();
                    $sgstAmount = $gstAddress->getSgstAmount();
                    $baseSgstAmount = $gstAddress->getBaseSgstAmount();
                    $utgstAmount = $gstAddress->getUtgstAmount();
                    $baseUtgstAmount = $gstAddress->getBaseUtgstAmount();
                } else {
                    $igstAmount = $quote->getIgstAmount();
                    $baseIgstAmount = $quote->getBaseIgstAmount();
                    $cgstAmount = $quote->getCgstAmount();
                    $baseCgstAmount = $quote->getBaseCgstAmount();
                    $sgstAmount = $quote->getSgstAmount();
                    $baseSgstAmount = $quote->getBaseSgstAmount();
                    $utgstAmount = $quote->getUtgstAmount();
                    $baseUtgstAmount = $quote->getBaseUtgstAmount();
                }
                if ($this->helper->canApplyIgst($gstAddress)) {
                    $order->setIgstAmount($igstAmount);
                    $order->setBaseIgstAmount($baseIgstAmount);
                } else {
                    $order->setCgstAmount($cgstAmount);
                    $order->setBaseCgstAmount($baseCgstAmount);
                    if ($isUnionTarritotial) {
                        $order->setUtgstAmount($utgstAmount);
                        $order->setBaseUtgstAmount($baseUtgstAmount);
                    } else {
                        $order->setSgstAmount($sgstAmount);
                        $order->setBaseSgstAmount($baseSgstAmount);
                    }
                }

            }

            if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                $shippingIgstAmount = $gstAddress->getShippingIgstAmount();
                $shippingBaseIgstAmount = $gstAddress->getBaseShippingIgstAmount();
                $shippingCgstAmount = $gstAddress->getShippingCgstAmount();
                $shippingBaseCgstAmount = $gstAddress->getBaseShippingCgstAmount();
                $shippingSgstAmount = $gstAddress->getShippingSgstAmount();
                $shippingBaseSgstAmount = $gstAddress->getBaseShippingSgstAmount();
                $shippingUtgstAmount = $gstAddress->getShippingUtgstAmount();
                $shippingBaseUtgstAmount = $gstAddress->getBaseShippingUtgstAmount();
            } else {
                $shippingIgstAmount = $quote->getShippingIgstAmount();
                $shippingBaseIgstAmount = $quote->getBaseShippingIgstAmount();
                $shippingCgstAmount = $quote->getShippingCgstAmount();
                $shippingBaseCgstAmount = $quote->getBaseShippingCgstAmount();
                $shippingSgstAmount = $quote->getShippingSgstAmount();
                $shippingBaseSgstAmount = $quote->getBaseShippingSgstAmount();
                $shippingUtgstAmount = $quote->getShippingUtgstAmount();
                $shippingBaseUtgstAmount = $quote->getBaseShippingUtgstAmount();
            }
            if ($this->helper->isShippingGst()) {
                if ($this->helper->canApplyCgstShipping($gstAddress)) {
                    $order->setShippingCgstAmount($shippingCgstAmount);
                    $order->setBaseShippingCgstAmount($shippingBaseCgstAmount);
                    if ($isUnionTarritotial) {
                        $order->setShippingUtgstAmount($shippingUtgstAmount);
                        $order->setBaseShippingUtgstAmount($shippingBaseUtgstAmount);
                    } else {
                        $order->setShippingSgstAmount($shippingSgstAmount);
                        $order->setBaseShippingSgstAmount($shippingBaseSgstAmount);
                    }
                } else {
                    $order->setShippingIgstAmount($shippingIgstAmount);
                    $order->setBaseShippingIgstAmount($shippingBaseIgstAmount);
                }
            }
            $quoteRepository = $this->quoteRepository;

            $quote = $quoteRepository->get($order->getQuoteId());
            $customer = $this->customerFactory->create()->load($quote->getCustomer()->getId());

            if (!empty($customer->getId())) {
                if ($this->helper->getApplyGstOn() == ShippingBillingAddress::SHIPPING_ADDRESS) {
                    $addressId = $gstAddress->getCustomerAddressId();
                } else {
                    $addressId = $quote->getBillingAddress()->getCustomerAddressId();
                }
                if ($addressId) {
                    $addressObject = $this->addressRepository->getById($addressId);
                    $buyerGst = $addressObject->getCustomAttribute('buyer_gst_number');
                    if (empty($buyerGst)) {
                        $quote = $quoteRepository->get($order->getQuoteId());
                        $buyerGst = $quote->getBuyerGstNumber();
                    } else {
                        $buyerGst = $addressObject->getCustomAttribute('buyer_gst_number')->getValue();
                    }
                } else {
                    $buyerGst = $quote->getShippingAddress()->getBuyerGstNumber();
                }
            } else {
                $buyerGst = $quote->getShippingAddress()->getBuyerGstNumber();
            }
            $gstAddress->setBuyerGstNumber($buyerGst);

            if ($this->helper->isGstOnBilling()) {
                if (!empty($customer->getId())) {
                    $order->getBillingAddress()->setBuyerGstNumber($buyerGst);
                    $order->setBuyerGstNumber($buyerGst);
                } else {
                    $order->getBillingAddress()->setBuyerGstNumber($quote->getBuyerGstNumber());
                    $order->setBuyerGstNumber($quote->getBuyerGstNumber());
                }
            } else {
                $order->getShippingAddress()->setBuyerGstNumber($buyerGst);
                $order->setBuyerGstNumber($buyerGst);
            }
            $order->save();

        }
    }
}
