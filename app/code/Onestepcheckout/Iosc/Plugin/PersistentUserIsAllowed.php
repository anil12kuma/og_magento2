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
namespace Onestepcheckout\Iosc\Plugin;

class PersistentUserIsAllowed
{

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Onestepcheckout\Iosc\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Onestepcheckout\Iosc\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->userContext = $userContext;
        $this->helper = $helper;
    }

     /**
      * {@inheritDoc}
      */
    public function beforeIsAllowed($subject, \Magento\Quote\Api\Data\CartInterface $quote)
    {
        if ($this->helper->isEnabled()) {
            if ($quote->getCustomerId() &&
                !$this->userContext->getUserId() &&
                $this->userContext->getUserId() != $quote->getCustomerId()
            ) {

                $quote->setCustomerId(null);
                $quote->setCustomerIsGuest(true);
                $quote->setCustomerEmail(null);
                $quote->setCustomerGroupId(0);
                $quote->setCustomerFirstname(null);
                $quote->setCustomerLastname(null);
                $quote->setCustomerDob(null);
                $quote->setCustomerGender(null);
                $quote->setCustomerTaxvat(null);
                $quote->setCheckoutMethod('guest');

                foreach ($quote->getAllAddresses() as $address) {
                    $address->setCustomerId(null);
                    $address->setCustomerAddressId(null);
                }
            }

        }

        return [$quote];
    }
}
