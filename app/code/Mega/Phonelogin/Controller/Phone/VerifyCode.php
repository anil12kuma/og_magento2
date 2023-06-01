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

class VerifyCode extends  \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $_session;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $session
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_session = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $phone = $this->getRequest()->getParam('phone');
        $code = $this->getRequest()->getParam('code');

        $isValid = $this->_session->getData($phone);

        if($isValid) {
            if($code == $isValid){
                $this->_session->setData('mobile_verified', 1);
                $resp = array('status'=>true,'message'=> __('Mobile Number Has Been Verified'),'deb' => 'if');
                $this->messageManager->addSuccessMessage(__('Mobile Number Has Been Verified'));
                return $result->setData($resp);
                // @todo - make api call to wordpress
            }else{
                $this->_session->setData('mobile_verified', 0);
                $resp = array('status'=>false,'message'=> __('Invalid Verification Code'),'deb' => 'else');
                return $result->setData($resp);
            }
        }
        $this->_session->setData('mobile_verified', 0);
        $resp = array('status'=>false,'message'=> __('Invalid Verification Code'),'deb' => 'out');
        return $result->setData($resp);
    }
}