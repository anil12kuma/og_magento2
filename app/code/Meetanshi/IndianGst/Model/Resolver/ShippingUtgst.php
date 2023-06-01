<?php

namespace Meetanshi\IndianGst\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Meetanshi\IndianGst\Helper\Data;

/**
 * Class ShippingUtgst
 * @package Meetanshi\IndianGst\Model\Resolver
 */
class ShippingUtgst implements ResolverInterface
{
    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ShippingUtgst constructor.
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param Data $helperData
     */
    public function __construct(
        CartTotalRepositoryInterface $cartTotalRepository,
        Data $helperData
    )
    {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->helperData = $helperData;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $segments = [];

        if ($this->helperData->isEnabled()) {
            /** @var Quote $quote */
            $quote = $value['model'];
            $totals = $this->cartTotalRepository->get($quote->getId());
            $currencyCode = $quote->getQuoteCurrencyCode();
            foreach ($totals->getTotalSegments() as $segment) {
                if ($segment->getCode() == "shipping_utgst_amount") {
                    $data = $segment->getData();
                    $data['title'] = "CGST";
                    $data['currency'] = $currencyCode;
                    $segments[] = $data;
                }
            }
        }
        return $segments;
    }
}
