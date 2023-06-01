<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magestore\Storelocator\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url                      $customerUrl
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Shopbybrand\Model\SystemConfig $systemConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_systemConfig = $systemConfig;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $check = ($this->_systemConfig->isEnable() && $this->_systemConfig->isDisplayTopLink());

        return $check ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_storeManager->getStore()->getUrl(
            $this->_systemConfig->getFrontendUrlPath()
        );
    }
}