<?php

namespace Amasty\ShopbySeo\Plugin\Shopby\Model\UrlBuilder;

class Adapter
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    public function __construct(\Amasty\ShopbySeo\Helper\Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return string|null
     */
    public function afterGetSuffix($subject, $result)
    {
        if ($this->urlHelper->isAddSuffixToShopby()) {
            return $this->urlHelper->getSeoSuffix();
        }
        return $result;
    }
}
