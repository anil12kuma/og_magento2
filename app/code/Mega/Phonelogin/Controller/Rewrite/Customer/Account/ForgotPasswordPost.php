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

namespace Mega\Phonelogin\Controller\Rewrite\Customer\Account;


class ForgotPasswordPost extends  \Magento\Customer\Controller\Account\ForgotPasswordPost
{

    public function execute()
    {
        $helper = $this->_objectManager->create('Mega\Phonelogin\Helper\Data');
        if(!$helper->resetPasswordByPhoneEnabled()){
            return parent::execute();
        }
        $userName = $this->getRequest()->getParam('email');
        $isEmail = $helper->isEmail($userName);
        if($isEmail){
            return parent::execute();
        }else{
            $email = $helper->getEmailFromPhone($userName);
            if(!$email){
                return parent::execute();
            }
            $status  = $helper->sendPasswordResetLink($email,$userName);
            $this->_eventManager->dispatch('mega_phonelogin_sendverificationcode_after', ['event_data' => $status]);
            $this->messageManager
                ->addSuccessMessage(__('A password reset link has been sent on your mobile number'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
    }

}