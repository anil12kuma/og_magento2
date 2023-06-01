<?php
/**
 *
 * @category   MaxMage
 * @author     MaxMage Core Team <maxmagedev@gmail.com>
 * @date       3/12/2018
 * @copyright  Copyright © 2018 MaxMage. All rights reserved.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @file       LayoutProcessor.php
 */

namespace Mega\Phonelogin\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as MageLayoutProcessor;
use Mega\Phonelogin\Helper\CheckoutHelper;

class LayoutProcessor
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * LayoutProcessor constructor.
     * @param Data $helper
     */
    public function __construct(CheckoutHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param MageLayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(MageLayoutProcessor $subject, $jsLayout)
    {

        if (!$this->helper->isModuleEnabled()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            ['telephone'] = $this->helper->telephoneFieldConfig("shippingAddress");
        }

        return $jsLayout;
    }
}
