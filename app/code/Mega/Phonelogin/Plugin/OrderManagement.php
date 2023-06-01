<?php 
declare(strict_types=1);
namespace Mega\Phonelogin\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Customer\Model\Session;
use Mega\Phonelogin\Helper\CustomerHelper;


class OrderManagement
{

    protected $addressRepository;
    protected $layoutFactory;
    protected $_customerHelper;
    protected $_session;

    public function __construct(
        AddressRepositoryInterface $addressRepository,
        Session $session,
        CustomerHelper $customerHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->_session = $session;
        $this->_customerHelper = $customerHelper;
        $this->layoutFactory = $layoutFactory;
    }

  
    public function beforePlace(
        OrderManagementInterface $subject,
        OrderInterface $order
    ): array {

        try {
            $quoteId = $order->getQuoteId();
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phonelogin_address_log.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $address = $order->getShippingAddress();
            $mobileNumber = $address->getTelephone();
            $customerId = $this->_session->getCustomer()->getId();
            $isVerified = $this->_customerHelper->checkVerifiedNumber($customerId,$mobileNumber);
            $logger->info('checkMB-- '.$mobileNumber);
            if ($isVerified) {
                 $logger->info('already_verified_mobile_number - by sandy');
                 $this->_session->setData('mobile_verified', 1);
                 $this->_session->setData('mobile_verified_oncheckout', 1);
            }
            $addressId = $order->getShippingAddress()->getData('customer_address_id');
            $isValid = $this->_session->getData('mobile_verified');
            if(!$isValid){
                throw new \Magento\Framework\Exception\LocalizedException (__("Please Verify Your Mobile Number (".$mobileNumber.") before order place."));
            }
            if($addressId){
                $address = $this->addressRepository->getById($addressId);
                //$logger->info('not in not - by sandy v-'.$isVerified.' -- '.$this->_session->getData('mobile_verified').' -vvvvvv- '.$this->_session->getData('mobile_verified_oncheckout'));
                if ($this->_session->getData('mobile_verified') == 1 && $this->_session->getData('mobile_verified_oncheckout')) { 
                    $address->setCustomAttribute('is_mobile_verified', 1); 
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException (__("Please Verify Your Mobile Number (".$mobileNumber.") before order place."));
                }
                $address->setCustomAttribute('is_mobile_verified', 1); 
                $this->addressRepository->save($address);
            }
            $this->_session->setData('mobile_verified', 0);
            $this->_session->setData('mobile_verified_oncheckout', 0);
            return [$order];
         } catch (Exception $e) {
           
        }
    }
}
