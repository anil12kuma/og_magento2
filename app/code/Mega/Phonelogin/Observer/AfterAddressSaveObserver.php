<?php

namespace Mega\Phonelogin\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Framework\App\ResponseFactory;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\Message\ManagerInterface;

class AfterAddressSaveObserver implements ObserverInterface
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

    public function __construct(
        \Magento\Customer\Model\Session $customer,
        ResponseFactory $responseFactory,
        ManagerInterface $messageInterface,
        UrlInterface $urlInterface,
        AddressRepositoryInterface $addressRepository,
        Session $session
    )
    {
        $this->_userSession = $session;
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
        if ($customerAddress->getId()) {
        	$customerAddress->setIsMobileVerified(1);
        	$customerAddress->save();
	        $this->customer->setData('mobile_verified', 0); 
        }
        $this->customer->setData('mobile_verified', 0); 

    }
}
