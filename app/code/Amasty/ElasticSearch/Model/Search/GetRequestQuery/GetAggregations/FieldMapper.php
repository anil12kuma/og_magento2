<?php

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations;

class FieldMapper
{
    public const PRICE_ATTRIBUTE = 'price';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function mapFieldName($fieldName)
    {
        if ($fieldName == self::PRICE_ATTRIBUTE) {
            return $fieldName . '_' . $this->customerSession->getCustomerGroupId();
        }
        return $fieldName;
    }
}
