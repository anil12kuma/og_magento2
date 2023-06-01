<?php

namespace Dolphin\CartEdit\Helper;

use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const CART_EDIT_ENABLE = 'cartedit/general_option/enable_module';

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function setStoreScope()
    {
        return ScopeInterface::SCOPE_STORE;
    }

    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue(static::CART_EDIT_ENABLE, $this->setStoreScope());
    }

    public function getCustomLink($links)
    {
        return $this->_storeManager->getStore()->getUrl($links);
    }

    public function getCartEditAjaxUrl()
    {
        $links = 'cartedit/index/ajaxdata';
        return $this->getCustomLink($links);
    }

    public function getEditCartUrl()
    {
        $links = 'cartedit/index/edit';
        return $this->getCustomLink($links);
    }
}
