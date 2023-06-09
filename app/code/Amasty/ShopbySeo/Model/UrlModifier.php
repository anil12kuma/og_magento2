<?php

namespace Amasty\ShopbySeo\Model;

class UrlModifier implements \Amasty\ShopbyBase\Api\UrlModifierInterface
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
     * @param string $url
     * @return string
     */
    public function modifyUrl($url)
    {
        return $this->urlHelper->seofyUrl($url);
    }
}
