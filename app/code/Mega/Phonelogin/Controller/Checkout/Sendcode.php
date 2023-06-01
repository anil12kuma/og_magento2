<?php
namespace Mega\Phonelogin\Controller\Checkout;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Mega\Phonelogin\Helper\CustomerHelper;
class Sendcode extends  \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $_resourceConfig;
    protected $customer;
    protected $_customerHelper;

    /*
     *
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        CustomerHelper $customerHelper,
        \Magento\Customer\Model\Session $customer
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_resourceConfig = $resourceConfig;
        $this->customer = $customer;
        $this->_customerHelper = $customerHelper;
        parent::__construct($context);
    }


    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
         $this->customer->unsMobileVerified();
        $this->customer->unsMobileVerifiedOncheckout();
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phonelogin_address_log.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('Sendcode file'. ' -- ' .$this->customer->getData('mobile_verified').' -vvvvvv- '.$this->customer->getData('mobile_verified_oncheckout'));
        //$this->customer->setData('mobile_verified', 0);
        $result = $this->resultJsonFactory->create();
        $respArray = [];
        $mobilePhone = $this->getRequest()->getParam('phone');
        $mobilePhone = str_replace(' ', '', $mobilePhone);
       
        if(isset($mobilePhone)){
            $customerId = $this->customer->getCustomer()->getId();
            $isVerified = $this->_customerHelper->checkVerifiedNumber($customerId,$mobilePhone);
            if($isVerified){
                $this->customer->setData('mobile_verified', 1);
                $respArray['status'] = false;
                return $result->setData($respArray);
            }
           
        	$helper = $this->_objectManager->create('Mega\Phonelogin\Helper\Data');
            $status = $helper->sendVerificationCode($mobilePhone); 
            $this->_eventManager->dispatch('mega_phonelogin_sendverificationcode_after', ['event_data' => $status]);
            if ($status['status']) {
                $creditCounts = $helper->getConfiguration('mega_phonelogin/api/available_credits');
                $remainingCount = $creditCounts -1;
                if($remainingCount > 0)
                    $this->_resourceConfig->saveConfig(
                        'mega_phonelogin/api/available_credits',
                        $creditCounts-1,
                        'default',
                        0
                );
                $respArray['status'] = true;
                $respArray['message'] = __('A Verification code has been sent on your mobile number');
                $this->messageManager
                         ->addSuccessMessage(__('A Verification code has been sent on your mobile number'));
            } else {
                $respArray['status'] = false;
                $respArray['message'] = __('An Error occurred sending the verification code');
                $this->messageManager->addErrorMessage(__('An Error occurred sending the verification code'));
            }
            return $result->setData($respArray);
        }
        $respArray['status'] = false;
        $respArray['message'] = __('Error occurred sending the verification code');
        $this->messageManager->addErrorMessage(__('An Error occurred sending the verification code'));
        return $result->setData($respArray);
    }
}
