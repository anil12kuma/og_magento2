<?php

namespace Dev\RestApi\Plugin;

use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory as ProductRepository;

class OrderCustAttr
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param OrderExtensionFactory $extensionFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        OrderFactory $orderFactory,
        ProductRepository $productRepository
    ) {
        $this->orderExtensionFactory = $extensionFactory;
        $this->orderFactory = $orderFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Set "my_custom_order_attribute" to order data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function setMyCustomOrderAttributeData($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        if (!is_array($product)) {
            try {
                $productData = $this->productRepository->create()->get($product->getSku());
            } catch (\Exception $exception) {
                return;
            }
            
            $attribute = $productData->getResource()->getAttribute('image');
            $imageUrl  = $attribute->getFrontend()->getUrl($productData);
            $extensionAttributes = $product->getExtensionAttributes();
            $orderItemsExtensionAttributes = $extensionAttributes;

            $selectedOption = [];
            $selectedOptionIndex = 0;

            $options = $product->getProductOptions();
            $options = (isset($options['info_buyRequest'])) ? $options['info_buyRequest'] : [];

            $selectedOptionsValue = isset($options['super_attribute']) ? $options['super_attribute'] : [];

            foreach ($selectedOptionsValue as $key => $selectedOptionValue) {
                $attributeOptionCollection = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');
                $attributeId = $selectedOptionValue;
                $optionData = $attributeOptionCollection
                            ->setPositionOrder('asc')
                            ->setIdFilter($attributeId)
                            ->setStoreFilter()
                            ->load();

                $selectedOption[$selectedOptionIndex][$productAttributeRepository->get($key)->getAttributeCode()] = $optionData->getFirstItem()->getValue();
                $selectedOption[$selectedOptionIndex][$productAttributeRepository->get($key)->getAttributeCode()."_id"] = $selectedOptionValue;
            }
                
            $orderItemsExtensionAttributes->setImageUrl($imageUrl);
            $orderItemsExtensionAttributes->setBrandName($productData->getAttributeText('brand'));
            $orderItemsExtensionAttributes->setSelectedOptions(($selectedOption));
            
            $product->setExtensionAttributes($orderItemsExtensionAttributes);
        }
    }
    
    /**
     * Add "my_custom_order_attribute" extension attribute to order data object
     * to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $orderSearchResult
    ) {
        $orders = $orderSearchResult->getItems();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('Shiprocket\OrderDeliveryStatus\ViewModel\OrderStatus');
        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $order) {
                $order_id = $order->getData('increment_id');
                $shipping_order_status = $order->getData('status');
                if ($block->isEnabled()) {
                    $shipping_order_status = $block->showStatusByOrderId($order_id);
                    if($shipping_order_status == 'NEW' || $shipping_order_status != 'CANCELED' || !$shipping_order_status){
                        $shipping_order_status = $order->getData('status');
                    }
                }
                $extensionAttributes = $order->getExtensionAttributes();
                $extensionAttributes->setShippingStatus($shipping_order_status);
                $products = $order->getItems();
                if (is_array($products) && count($products) > 0) {
                    foreach ($products as $product) {
                        $this->setMyCustomOrderAttributeData($product); 
                    }
                }
            }
        }
        return $orderSearchResult;
    }

    /**
     * Add "my_custom_order_attribute" extension attribute to order data object
     * to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        $products = $resultOrder->getItems();
        if (is_array($products) && count($products) > 0) {
            $this->setMyCustomOrderAttributeData($products);
        }
        return $resultOrder;
    }
}