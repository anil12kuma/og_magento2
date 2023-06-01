<?php

namespace Dev\Auth\Controller\Token;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

class AutoLogin extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Customer\Model\Session
    */
    protected $_customerSession;
    protected $_customer;
    protected $resultRedirectFactory;
    protected $customerFactory;
    protected $customerRepository;
    protected $customerDataFactory;
    /**
    * @param Context          $context
    * @param Session          $customerSession
    * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    */
    public function __construct(            
        Context $context,
        Session $customerSession,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->_customer = $customer;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerFactory     = $customerFactory;
        $this->customerRepository  = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        // if(!$this->_customerSession->isLoggedIn())
        // {   
            $token = $this->getRequest()->getParam('token');
            $orderId = $this->getRequest()->getParam('orderId');
            
            $customerRepository =  $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
            $tokenFactory =  $objectManager->create('Magento\Integration\Model\Oauth\TokenFactory');
            $tokenObj = $tokenFactory->create()->loadByToken($token);

            $customerId = $tokenObj->getCustomerId();
            
            if (empty($customerId)) {
                $this->_customerSession->logout();
                $url = $this->storeManager->getStore()->getBaseUrl();
                $resultFactory =  $objectManager->create('\Magento\Framework\Controller\ResultFactory');
                $redirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $redirect->setUrl($url);
                return $redirect;
            }

            $this->_customer->setWebsiteId(1);
            $customer = $customerRepository->getById($customerId);
            $this->_customerSession->setCustomerDataAsLoggedIn($customer);
            
            
            if($this->_customerSession->isLoggedIn()){
                $cookieMetadataManager =  $objectManager->create('\Magento\Framework\Stdlib\Cookie\PhpCookieManager');
                $cookieMetadataFactory =  $objectManager->create('\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
                $this->_customerSession->setCustomerData($customer);

                if ($cookieMetadataManager->getCookie('mage-cache-sessid')) {
                    $metadata = $cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
                }

                $url = $this->storeManager->getStore()->getUrl('sales/order/print', ['order_id' => $orderId]);;

                $resultFactory =  $objectManager->create('\Magento\Framework\Controller\ResultFactory');
                $redirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $redirect->setUrl($url);
                return $redirect;

            }

        // }
        // else{
        //     $this->_customerSession->logout();
        //     $url = $this->storeManager->getStore()->getBaseUrl();
        //     $resultFactory =  $objectManager->create('\Magento\Framework\Controller\ResultFactory');
        //     $redirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        //     $redirect->setUrl($url);
        //     return $redirect;
        // }   
    }
}