<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Method;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\MethodProvider;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provide attribute codes used for sorting by amasty sorting.
 * This attributes must be added for indexation.
 */
class GetAttributeCodesForSorting
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MethodProvider
     */
    private $methodProvider;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    public function __construct(
        ConfigProvider $configProvider,
        MethodProvider $methodProvider,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->configProvider = $configProvider;
        $this->methodProvider = $methodProvider;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public function execute(): array
    {
        $attributes = [
            'created_at',
            'small_image',
            $this->configProvider->getBestsellerAttributeCode(),
            $this->configProvider->getMostviewedAttributeCode(),
            $this->configProvider->getNewAttributeCode(),
            $this->configProvider->getGlobalSorting()
        ];

        return array_filter($attributes, function (?string $attribute) {
            return $attribute !== null && $this->isEavAttribute($attribute);
        });
    }

    /**
     * Check if eav attribute exists.
     *
     * Detect if passed attribute code need be processed with magento.
     * If amasty method exist with same code, amasty method more priority and processed by Sorting module.
     */
    private function isEavAttribute(string $sortAttributeCode): bool
    {
        if ($this->methodProvider->getMethodByCode($sortAttributeCode) !== null) {
            return false;
        }
        
        try {
            $this->productAttributeRepository->get($sortAttributeCode);
            $result = true;
        } catch (NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }
}
