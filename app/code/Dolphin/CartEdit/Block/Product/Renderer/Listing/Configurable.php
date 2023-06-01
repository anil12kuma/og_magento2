<?php

namespace Dolphin\CartEdit\Block\Product\Renderer\Listing;

use Magento\Swatches\Block\Product\Renderer\Configurable as ParentConfigurable;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    protected function getSwatchAttributesData()
    {
        $result = [];
        $swatchAttributeData = ParentConfigurable::getSwatchAttributesData();
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
        foreach ($swatchAttributeData as $attributeId => $item) {
            if (!empty($item['used_in_product_listing'])) {
                $result[$attributeId] = $item;
            } elseif ($request->getFullActionName() == 'cartedit_index_ajaxdata') {
                $result[$attributeId] = $item;
            }
        }
        return $result;
    }
}