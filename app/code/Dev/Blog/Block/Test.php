<?php

namespace Dev\Blog\Block;

use Magento\Store\Model\StoreManagerInterface;

class Test extends \Magento\Framework\View\Element\Template
{
    /**
     * @var QuoteEmail
     */
    private $storeManager;
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    public function getCurrentUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl();
    }
}
