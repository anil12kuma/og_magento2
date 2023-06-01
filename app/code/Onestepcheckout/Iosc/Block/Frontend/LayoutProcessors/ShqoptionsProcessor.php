<?php
/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
namespace Onestepcheckout\Iosc\Block\Frontend\LayoutProcessors;

class ShqoptionsProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Onestepcheckout\Iosc\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Onestepcheckout\Iosc\Helper\Data $helper
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {

        if ($this->helper->isEnabled()) {

            $layoutPath = $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['shipping-step']
                ['children']['shippingAddress']
                ['children']['shippingAdditional']
                ['children']['option_additional_block']['component'] ?? false;
            if ($layoutPath && $layoutPath === 'ShipperHQ_Option/js/view/checkout/shipping/shipperhq-option') {
                $jsLayout['components']['checkout']
                    ['children']['steps']
                    ['children']['shipping-step']
                    ['children']['shippingAddress']
                    ['children']['shippingAdditional']
                    ['children']['shqoptionsProcessor']['component'] =  'Onestepcheckout_Iosc/js/shqoptions';
            }

        }

        return $jsLayout;
    }
}
