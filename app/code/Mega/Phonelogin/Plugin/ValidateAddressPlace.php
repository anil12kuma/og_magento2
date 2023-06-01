<?php

namespace Mega\Phonelogin\Plugin;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Mega\Phonelogin\Helper\CustomerHelper;

class ValidateAddressPlace
{
    protected $_coreSession;

    protected $_logger;
    protected $_customerHelper;
    protected $customer;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customer,
         CustomerHelper $customerHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_coreSession = $coreSession;
        $this->_customerHelper = $customerHelper;
        $this->customer = $customer;
        $this->_logger = $logger;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     * @throws InputException
     */
    public function beforeSaveAddressInformation(ShippingInformationManagement $subject, $cartId, ShippingInformationInterface $addressInformation): array
    {
        $isVerifiedOnCheckout = $this->customer->getData('mobile_verified_oncheckout');
        if($isVerifiedOnCheckout){
           $this->customer->setData('mobile_verified', 1); 
           return [$cartId, $addressInformation]; 
        }
        $address = $addressInformation->getShippingAddress();
        $mobileNumber = $address->getTelephone();
        $customerId = $this->customer->getCustomer()->getId();
        $isVerified = $this->_customerHelper->checkVerifiedNumber($customerId,$mobileNumber);
        // if(!$isVerified){
        //      $this->customer->setData('mobile_verified', 0);
        //      throw new InputException(__("Please Verify Your Mobile Number from address edit page."));
        // }
        $this->customer->setData('mobile_verified', 1);
        return [$cartId, $addressInformation];
    }

}

