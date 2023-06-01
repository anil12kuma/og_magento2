<?php

namespace Dev\Auth\Controller\Token;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class CreateToken extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Customer\Model\Session
    */
    protected $_customerSession;

    /**
    * @param Context          $context
    * @param Session          $customerSession
    * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    */
    public function __construct(            
        Context $context,
        Session $customerSession,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_tokenModelFactory = $tokenModelFactory;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        // echo 'hello';
        // $customerId = $this->_customerSession->getCustomer()->getId();
        $customerToken = $this->_tokenModelFactory->create();
        echo "Customer-token=> ".$tokenKey = $customerToken->createCustomerToken(20)->getToken();
    }
}