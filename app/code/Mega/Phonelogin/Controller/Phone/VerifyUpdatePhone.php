<?php
/**
 * Created by PhpStorm.
 * User: simplysaif
 * Date: 30/5/18
 * Time: 11:57 AM
 */

namespace Mega\Phonelogin\Controller\Phone;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use \Magento\Customer\Model\Session;

class VerifyUpdatePhone extends  \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $_session;
    protected $_customerDataFactory;
    protected $_customerRepositoryInterface;
    protected $_customerRepository;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $session,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_session = $session;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $phone = $this->getRequest()->getParam('phone');
        $phone = str_replace(' ', '', $phone);
        $code = $this->getRequest()->getParam('code');

        $isValid = $this->_session->getData($phone);

        if($isValid) {
            if($code == $isValid){
                $this->_session->setData('mobile_verified', 1);
                $customerId = $this->_session->getCustomer()->getId();
                // $customer = $this->_customerRepositoryInterface->getById($customerId);
                // $customer->setCustomAttribute('mphone_number', $phone);
                // $this->_customerRepositoryInterface->save($customer);
                $this->_session->setData('mobile_verified_oncheckout', 1);
                $resp = array('status'=>true,'message'=> __('Mobile Number Has Been Verified'));
                $this->messageManager->addSuccessMessage(__('Mobile Number Has Been Verified'));

                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phonelogin_address_log.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('tst code IN ');
                $logger->info('mobile_verified_oncheckout ==>'.$this->_session->getData('mobile_verified_oncheckout'));
                $logger->info('mobile_verified ==>'.$this->_session->getData('mobile_verified'));
                return $result->setData($resp);
            }else{
                $this->_session->setData('mobile_verified', 0);
                $this->_session->setData('mobile_verified_oncheckout', 0);
                $resp = array('status'=>false,'message'=> __('Invalid Verification Code'));
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phonelogin_address_log.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('tst code NO ');
                $logger->info('NO_mobile_verified_oncheckout ==>'.$this->_session->getData('mobile_verified_oncheckout'));
                $logger->info('NO_mobile_verified ==>'.$this->_session->getData('mobile_verified'));

                return $result->setData($resp);
            }
        }
        $this->_session->setData('mobile_verified_oncheckout', 0);
        $this->_session->setData('mobile_verified', 0);
        $resp = array('status'=>false,'message'=> __('Invalid Verification Code'),'deb' => 'out');
        return $result->setData($resp);
    }

}