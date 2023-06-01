<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to samparker801@gmail.com so we can send you a copy immediately.
 *
 * @category    Mega
 * @package     Mega_PhoneLogin
 * @copyright   Copyright (c) 2017 Sam Parker (samparker801@gmail.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mega\Phonelogin\Controller\Phone;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use \Magento\Customer\Model\Session;
class Sendcode extends  \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $_resourceConfig;
    protected $_session;

    /*
     *
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        Session $session
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_resourceConfig = $resourceConfig;
        $this->_session = $session;
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


        $this->_session->setData('mobile_verified', 0);
        $result = $this->resultJsonFactory->create();
        $respArray = ['status'=>false,'message'=>'An Error Occurred','code'=>'01'];
        /*
         * code 01 - duplicate phone number
         */

        if($this->getRequest()->isAjax()){
            $mobilePhone = $this->getRequest()->getParam('phone');
            $helper = $this->_objectManager->create('Mega\Phonelogin\Helper\Data');
            if ($helper->phoneIsUnique($mobilePhone)) {
                $this->messageManager
                    ->addErrorMessage(
                        sprintf(
                            __('%s is already associated with an account. You may reset password or chose other mobile number'),
                                $mobilePhone)
                    );
                $respArray['mesage'] = __('This Mobile Number is already associated with an account');
            } else {
                if($this->getRequest()->getParam('code',false)){
                    $phoneValidator = new \Zend\I18n\Validator\PhoneNumber(['country'=> $this->getRequest()->getParam('code') ]);
                }else {
                    $phoneValidator = new \Zend\I18n\Validator\PhoneNumber();
                }
                if(!$phoneValidator->isValid($mobilePhone)){
                    $respArray['status'] = false;
                    $respArray['message'] = __('Mobile Number is invalid');
                    $this->messageManager->addErrorMessage(__('An Error occurred sending the verification code'));
                    return $this->resultJsonFactory->create()->setData($respArray);
                }
                $status = $helper->sendVerificationCode($mobilePhone);

                $this->_eventManager->dispatch('mega_phonelogin_sendverificationcode_after', ['event_data' => $status]);
                if ($status['status']) {
                    $respArray['status'] = true;
                    $respArray['message'] = __('A Verification code has been sent on your mobile number');
                    $this->messageManager
                         ->addSuccessMessage(__('A Verification code has been sent on your mobile number'));
                } else {
                    $respArray['status'] = false;
                    $respArray['message'] = __('An Error occurred sending the verification code');
                    $this->messageManager->addErrorMessage(__('An Error occurred sending the verification code'));
                }
            }
            return $result->setData($respArray);
        }
    }
}
