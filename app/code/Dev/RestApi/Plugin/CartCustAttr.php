<?php

namespace Dev\RestApi\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory as ProductRepository;

class CartCustAttr
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        CartItemExtensionFactory $cartItemExtension, 
        ProductRepository $productRepository
    ) {
        $this->cartItemExtension = $cartItemExtension;
        $this->productRepository = $productRepository;
    }

    /**
     * Add attribute values
     *
     * @param CartRepositoryInterface $subject ,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(CartRepositoryInterface $subject, $quote)
    {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * Add attribute values
     *
     * @param CartRepositoryInterface $subject ,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActiveForCustomer(CartRepositoryInterface $subject, $quote)
    {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote) {
        $data = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');

        if ($quote->getItemsCount()) {
            $items = $quote->getItems();
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) { 
                    $data = [];
                    $extensionAttributes = $item->getExtensionAttributes();
                    if ($extensionAttributes === null) {
                        $extensionAttributes = $this->cartItemExtension->create();
                    }

                    $productData = $this->productRepository->create()->get($item->getSku());
                    $discount = ((int) $productData->getSpecialPrice() && (int) $productData->getPrice()) ? round(100 - $productData->getSpecialPrice() / (int) $productData->getPrice() * 100) . '%' : "0%";
                    $qty = $productData->getQuantityAndStockStatus();
                    $attribute = $productData->getResource()->getAttribute('image');
                    $imageUrl  = $attribute->getFrontend()->getUrl($productData);
                    $extensionAttributes->setImageUrl($imageUrl);
                    $extensionAttributes->setPrice((int) $productData->getPrice());
                    $extensionAttributes->setSpecialPrice((int) $productData->getSpecialPrice());
                    $extensionAttributes->setDiscount($discount);
                    $extensionAttributes->setTotalQty($qty['qty']);

                    $option = [];
                    $optionIndex = 0;
                    $selectedOption = [];
                    $selectedOptionIndex = 0;

                    $options = $item->getOptions();
                    $options = (isset($options[0]) && isset($options[0])) ? json_decode($options[0]->getValue()) : 0;
                    $parentId = ($options && isset($options->product)) ? $options->product : 0;

                    if (!$parentId) {
                        $parentIds = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                                                ->getParentIdsByChild($productData->getData('entity_id'));
                        $parentId = array_shift($parentIds);
                    }

                    $selectedOptionsValue = isset($options->super_attribute) ? json_decode(json_encode($options->super_attribute),true) : [];

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

                    if ($parentId && isset($parentId)) {
                        $product = $objectManager->get('\Magento\Catalog\Model\Product')->load($item->getSku());
                        $repository = $objectManager->create('Magento\Catalog\Model\ProductRepository');
                        $product = $repository->getById($parentId);

                        if ($product->getData('type_id') === 'configurable') {
                            $attributes = [];
                            $attributes = $product->getTypeInstance()->getConfigurableOptions($product);
                            //get the configurable product its childproducts
                            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                            if (!empty($attributes)) {
                                foreach($attributes as $attribute){
                                    foreach ($attribute as $key => $attr) {
                                            $option[$optionIndex]['name'] = $attr['attribute_code'];
                                            $option[$optionIndex]['name_sku'] = $productAttributeRepository->get($attr['attribute_code'])->getAttributeId();
                                            $option[$optionIndex]['option_title'][$key][$attr['attribute_code']] = $attr['option_title'];
                                            $option[$optionIndex]['option_title'][$key]['id'] = $attr['value_index'];
                                            if($attr['attribute_code'] === 'color'){
                                                foreach($childProducts as $childProduct){
                                                    if ($attr['value_index'] !== $childProduct->getData('color')) {
                                                        continue;
                                                    }
                                                    $childAttributeSwatch = $childProduct->getResource()->getAttribute('small_image');
                                                    $childSwatchImageUrl  = $childAttributeSwatch->getFrontend()->getUrl($childProduct);
                                                    $option[$optionIndex]['option_title'][$key]['swatch_image_url'] = $childSwatchImageUrl;
                                                }
                                            }
                                    }
                                    if(array_key_exists($optionIndex, $option) && array_key_exists('option_title', $option[$optionIndex]))
                                        $option[$optionIndex]['option_title'] = array_values(array_unique($option[$optionIndex]['option_title'], SORT_REGULAR));
                                    $optionIndex++;
                                }
                            }
                        }
                    }

                    $extensionAttributes->setSelectedOptions(($selectedOption));
                    $extensionAttributes->setOptions(($option));
                    $item->setExtensionAttributes($extensionAttributes);
                }
            }
        }

        return $quote;
    }
}