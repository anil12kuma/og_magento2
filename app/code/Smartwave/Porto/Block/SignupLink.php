<?php

namespace Smartwave\Porto\Block;

use Magento\Framework\View\Element\Html\Link;
use Magento\Customer\Model\Context;

/**
 * Class SignupLink
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class SignupLink extends Link
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {
        $this->_customerUrl = $customerUrl;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_customerUrl->getLoginUrl();
    }


    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    protected function _toHtml()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return '';
        }

        return parent::_toHtml();
    }
}