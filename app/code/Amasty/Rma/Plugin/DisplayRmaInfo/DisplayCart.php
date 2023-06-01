<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Plugin\DisplayRmaInfo;

use Amasty\Rma\Model\ConfigProvider;
use Amasty\Rma\Model\ReturnRules\ReturnRulesProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class DisplayCart
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ReturnRulesProcessor
     */
    private $returnRulesProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ConfigProvider $configProvider,
        ReturnRulesProcessor $returnRulesProcessor,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->returnRulesProcessor = $returnRulesProcessor;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Item\Renderer $subject
     * @param array $result
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetOptionList($subject, $result)
    {
        if (!$this->configProvider->isEnabled()
            || !$this->configProvider->isShowRmaInfoCart($this->storeManager->getStore()->getId())
        ) {
            return $result;
        }
        /**
         * Reload product with attributes from repository for rule validation
         * @see ReturnRulesProcessor::getRuleToApply()
         */
        $product = $this->productRepository->getById($subject->getProduct()->getId());
        $resolutions = [];

        if (!$subject->getProduct()->isVirtual()) {
            $resolutions = $this->returnRulesProcessor->getResolutionsForProduct($product);
        }

        if ($resolutions) {
            foreach ($resolutions as $resolutionData) {
                $result[] = [
                    'label' => __('%1 period', $resolutionData['resolution']->getLabel()),
                    'value' => __('%1 days', $resolutionData['value'])
                ];
            }
        } else {
            $result[] = [
                'label' => __('Item Returns'),
                'value' => is_array($resolutions)
                    ? __('Sorry, the item can\'t be returned')
                    : __('This item can be returned')
            ];
        }

        return $result;
    }
}
