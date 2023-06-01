<?php

namespace Mega\Phonelogin\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Framework\App\ResponseFactory;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\Message\ManagerInterface;
use Mega\Phonelogin\Helper\CustomerHelper;

class SaveAddressBefore implements ObserverInterface
{

    /**
     * @var Session
     */
    private $_userSession;
    protected $customer;
    protected  $messageManager;
    protected $addressRepository;
    protected  $responseFactory;
     protected  $urlInterface;
     protected $_customerHelper;

    public function __construct(
        \Magento\Customer\Model\Session $customer,
        ResponseFactory $responseFactory,
        ManagerInterface $messageInterface,
        CustomerHelper $customerHelper,
        UrlInterface $urlInterface,
        AddressRepositoryInterface $addressRepository,
        Session $session
    )
    {
        $this->_userSession = $session;
        $this->_customerHelper = $customerHelper;
        $this->responseFactory = $responseFactory;
        $this->addressRepository = $addressRepository;
        $this->messageManager = $messageInterface;
        $this->customer = $customer;
        $this->urlInterface = $urlInterface;
    }


    public function execute(Observer $observer)
    {

        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();
        $mobileNumber = $customerAddress->getTelephone();
        $customerId = $customer->getId();
        $isVerified = $this->_customerHelper->checkVerifiedNumber($customerId,$mobileNumber);
        if ($isVerified) {
                 $this->customer->setData('mobile_verified', 1); 
        }
        $isValid = $this->customer->getData('mobile_verified');
        if(!$isValid){
            $redirectionUrl = $this->urlInterface->getUrl('customer/address/new/');
            $this->messageManager->addErrorMessage(__('Please Verify Your Mobile Number'));
            $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            die;
            return $this;
        }else{
            $customerAddress->setIsMobileVerified(1);
            $this->customer->setData('mobile_verified', 0); 
        }

    }
}
