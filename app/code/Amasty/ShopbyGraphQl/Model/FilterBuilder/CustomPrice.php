<?php

declare(strict_types=1);

namespace Amasty\ShopbyGraphQl\Model\FilterBuilder;

use Amasty\ShopbyGraphQl\Model\FilterBuilderInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Directory\Model\PriceCurrency;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class CustomPrice implements FilterBuilderInterface
{
    public const BACKEND_TYPE_DECIMAL = 'decimal';
    public const ATTRIBUTE_PRICE = 'price';

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    public function __construct(
        EavConfig $eavConfig,
        PriceCurrency $priceCurrency
    ) {
        $this->eavConfig = $eavConfig;
        $this->priceCurrency = $priceCurrency;
    }

    public function build(array &$filters, int $storeId): void
    {
        foreach ($filters as &$filter) {
            $attribute = $this->eavConfig->getAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $this->getAttributeCode($filter)
            );
            if ($attribute->getBackendType() == self::BACKEND_TYPE_DECIMAL
                && $attribute->getAttributeCode() != self::ATTRIBUTE_PRICE
            ) {
                $filter['attribute_code'] = $this->getAttributeCode($filter);
                $filter['label'] = $this->getFrontendLabel($attribute, $storeId);
                foreach ($filter['options'] as &$value) {
                    [$from, $to] = explode('_', $value['label']);
                    $newLabel = $this->priceCurrency->convertAndRound($from)
                        . '-'
                        . $this->priceCurrency->convertAndRound($to);
                    $value['label'] = $newLabel;
                    $value['value'] = str_replace('-', '_', $newLabel);
                }
            }
        }
    }

    private function getFrontendLabel(AbstractAttribute $attribute, int $storeId): string
    {
        $attributeLabel = '';
        $labels = $attribute->getFrontendLabels();
        foreach ($labels as $label) {
            if ($label->getStoreId() == $storeId) {
                $attributeLabel = $label->getLabel();
            }
        }

        return $attributeLabel ?: $attribute->getDefaultFrontendLabel();
    }

    private function getAttributeCode(array $filter): string
    {
        return str_replace('_bucket', '', $filter['attribute_code']);
    }
}
