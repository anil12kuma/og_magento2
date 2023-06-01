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
namespace Mega\Phonelogin\Helper;


use Braintree\Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use \Magento\Customer\Model\Session;


class Data  extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected  $_objectManager;
    protected  $_curl;
    protected  $_scopeConfigManager;
    protected  $_userSession;


    /**
     * @var \Magento\Framework\Module\Manager
     */
    public function __construct(
        Context $context,\Magento\Framework\ObjectManagerInterface $objectManager,
        Curl $curl,
        Session $session
    )
    {
        $this->_objectManager = $objectManager;
        $this->_curl = $curl;
        $this->_scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_userSession = $session;
        parent::__construct($context);
    }


    public function getCustomerCollection(){
        $customerFactory = $this->_objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory');
        $customerCollection = $customerFactory->create();
        return $customerCollection;
    }


    public function phoneIsUnique($phone){
        $customerCollection = $this->getCustomerCollection()
                                    ->addAttributeToSelect('*');
        $customerCollection->addAttributeToFilter('mphone_number', $phone);
        return $customerCollection->getSize();
    }

    /*
     * @param string
     * @returns array
     */
    public function sendVerificationCode($phone){
        try{
            $code = mt_rand(100000, 999999);
            $message = $this->getVerificationText();
            $message = str_replace('{{verification_code}}', $code, $message);
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/otp_log.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('message----'.$message);
            $status = $this->sendMessage($phone, $message);
            $this->_userSession->setData($phone, $code);
            $this->_userSession->setData('mobile_verified', 0);
        }catch (\Exception $e){
            $this->_logger->debug($e->getMessage());
            return array('status'=> false, 'message' => $message.'--'.$e->getMessage(), 'to' => $phone);
        }

        return array('status'=> $status, 'message' => $message, 'to' => $phone);
        //return array('status'=> true, 'message' => $message, 'to' => $phone);

    }


    /*
     *
     */
    public function sendMessage($phone, $message)
    {
        $apiProvider = $this->getConfiguration('mega_phonelogin/api/provider');
        $email = $this->getConfiguration('trans_email/ident_support/email');
        $name = $this->getConfiguration('trans_email/ident_support/name');
        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $domain = $storeManager->getStore()->getBaseUrl();
        $url = $this->getConfiguration('mega_phonelogin/api/innourl');
        $arr = array('mail'=>$email,'name'=>$name,'domain'=>$domain);
        $url .= '?params='.json_encode($arr);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        curl_close($ch);
        if($apiProvider == 1){
            $id = $this->getConfiguration('mega_phonelogin/api/account_id');
            $token = $this->getConfiguration('mega_phonelogin/api/token');
            $url = "https://api.twilio.com/2010-04-01/Accounts/'.$id.'/SMS/Messages.json";
            $from = $this->getConfiguration('mega_phonelogin/api/twilio_number');
            $to = $phone;
            $body = $message;
            $data = array (
                'From' => $from,
                'To' => $to,
                'Body' => $body,
            );
            $post = http_build_query($data);
            $ch = curl_init($url );
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$id:$token");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec($ch);
            curl_close($ch);
            return true;
        }elseif($apiProvider == 2){
            $url =  $this->getConfiguration('mega_phonelogin/api/routee_url');
            $key =  $this->getConfiguration('mega_phonelogin/api/routee_key');
            $secret =  $this->getConfiguration('mega_phonelogin/api/routee_secret');
            $senderId = $this->getConfiguration('mega_phonelogin/api/routee_sender_id');
            $authorizationKey = base64_encode($key.':'.$secret);
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://auth.routee.net/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic {$authorizationKey}",
                "content-type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $errorM =  "cURL Error #:" . $err;
                $this->_logger->debug($errorM);
                return false;
            } else {

            }
            $response = json_decode($response);
            $authorizationKey = $response->access_token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{ \"body\": \"$message\",\"to\" : \"$phone\",\"from\": \"$senderId\"}",
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer $authorizationKey",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $errorM =  "cURL Error #:" . $err;
                $this->_logger->debug($errorM);
                return false;
            } else {
                return true;
            }
        }elseif($apiProvider == 3){
            $url = $this->scopeConfig->getValue('mega_phonelogin/api/msg91_url');
            $authKey = $this->scopeConfig->getValue('mega_phonelogin/api/msg91_api_key');
            $senderId = $this->scopeConfig->getValue('mega_phonelogin/api/msg91_sender_id');
            $message = urlencode($message);
            $route = "default";
            $postData = array(
                'authkey' => $authKey,
                'mobiles' => $phone,
                'message' => $message,
                'sender' => $senderId,
                'route' => $route
            );

            #$url="https://control.msg91.com/api/sendhttp.php";

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData
                //,CURLOPT_FOLLOWLOCATION => true
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
            if(curl_errno($ch))
            {
                $errorM =  "cURL Error #:" . curl_error($ch);
                $this->_logger->debug($errorM);
                return false;
            }
            curl_close($ch);
            return true;
        }elseif($apiProvider == 4){

            $params['username'] = $this->scopeConfig->getValue('mega_phonelogin/api/serwer_username');
            $params['password'] = $this->scopeConfig->getValue('mega_phonelogin/api/serwer_password');
            $params['phone'] = $phone;
            $params['text'] = $message;
            $params['sender'] = $this->scopeConfig->getValue('mega_phonelogin/api/serwer_sender');
            $params['system'] = 'client_php';
            $testMode = $this->scopeConfig->getValue('mega_phonelogin/api/test_mode');
            if($testMode){
                $params['test'] = 1;
            }
            $curl = curl_init('https://api2.serwersms.pl' . '/' . 'messages/send_sms.json');

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $resp = curl_exec($curl);

            if (curl_errno($curl)) {
                $errorM = 'Failed call: ' . curl_error($curl);
                $this->_logger->debug($errorM);
                curl_close($curl);
                return false;
            }
            curl_close($curl);
            $resp = json_decode($resp,true);

            if($resp['success'] == 1)
                return true;
           return true;

        }elseif($apiProvider == 6){
            $userName = $this->scopeConfig->getValue('mega_phonelogin/api/whistle_username');
            $password = $this->scopeConfig->getValue('mega_phonelogin/api/whistle_password');
            $senderId = $this->scopeConfig->getValue('mega_phonelogin/api/whistle_senderid');
            $message = urlencode($message);
            $url = 'http://smpp.whistle.mobi/sendsms.jsp?user='.$userName.'&password='.$password.'&senderid='.$senderId.'&mobiles='.$phone.'&sms='.$message;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            // curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            $new = simplexml_load_string($response);
            $con = json_encode($new);
            $response = json_decode($con, true);
            if($response['sms']['status'] == 'success'){
                return true;
            }else{
                return false;

            }
           
        } else{
            $url = $this->getConfiguration('mega_phonelogin/api/url');
            $apiKey = $this->getConfiguration('mega_phonelogin/api/key');
            $sender = $this->getConfiguration('mega_phonelogin/api/sender_id');
            $url .= '?apikey='.$apiKey.'&sender='.$sender;
            $url .= '&to='.$phone.'&message='.urlencode($message).'&format=json';
            $this->_logger->debug($url);

            $this->_curl->get($url);
            $curlResponse = $this->_curl->getBody();
            $curlResponse = json_decode($curlResponse);

            if($curlResponse->status){
                return true;
            }else{
                $this->_logger->debug($curlResponse->error);
                return false;
            }
        }
    }


    public function getVerificationText()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('mega_phonelogin/templates/verification', $storeScope);
    }


    public function getConfiguration($config){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($config, $storeScope);
    }


    public function isEmail($mail){
        $validator = new \Zend\Validator\EmailAddress();
        $isValid = $validator->isValid($mail);
        if(($isValid)){
            return true;
        }
        return false;
    }


    public function getEmailFromPhone($phone){
        $customerFactory = $this->_objectManager->create('\Magento\Customer\Model\CustomerFactory');
        $customerCollection = $customerFactory->create()
                                ->getCollection()
                                ->addAttributeToSelect("*")
                                ->addAttributeToFilter("mphone_number", array("eq" => $phone))
                                ->load();
        if($customerCollection->getFirstItem()->getEmail())
            return $customerCollection->getFirstItem()->getEmail();
        else
            return false;
    }


    public function sendPasswordResetLink($email,$phone){
        try{
            $customerHelper = $this->_objectManager->create('Magento\User\Helper\Data');
            $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $baseUrl = $storeManager->getStore()->getBaseUrl();
            $token = $customerHelper->generateResetPasswordLinkToken();
            $user = $this->getUserFromPhone($email);
            $id  = $user->getEntityid();
            $user->setRpToken($token)->save();
            $resetLink = $baseUrl.'customer/account/createPassword/?id='.$id.'&token='.$token;
            $resetMessage = $this->getPasswordResetMessage($resetLink);
            $this->sendMessage($phone,$resetMessage);
            return array('status'=> true, 'message' => $resetMessage, 'to' => $phone);
        }catch (\Exception $e){
            return array('status'=> false, 'message' => $resetMessage.'--'.$e->getMessage(), 'to' => $phone);
        }


    }


    public function getPasswordResetMessage($link){
        $resetTemplate = $this->getConfiguration('mega_phonelogin/templates/reset_password');
        $message = str_replace('{{password_reset_link}}',$link, $resetTemplate);
        return $message;
    }


    public function getUserFromPhone($email){
        $customerFactory = $this->_objectManager->create('\Magento\Customer\Model\CustomerFactory');
        $customer = $customerFactory->create()
            ->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("email", array("eq" => $email))
            ->load();

        return $customer->getFirstItem();
    }


    public function moduleIsEnabled(){
        return $this->getConfiguration('mega_phonelogin/general/activation');
    }


    public function loginByMobileEnabled(){
        $enabled = $this->getConfiguration('mega_phonelogin/general/login_activation');
        if($this->moduleIsEnabled() && $enabled)
            return true;
        return false;
    }

    public function resetPasswordByPhoneEnabled(){
        $enabled = $this->getConfiguration('mega_phonelogin/general/password_activation');
        if($this->moduleIsEnabled() && $enabled)
            return true;
        return false;
    }

    public function getUserByMobile($mobile){
        $customerFactory = $this->_objectManager->create('\Magento\Customer\Model\CustomerFactory');
        $customerCollection = $customerFactory->create()
            ->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("mphone_number", array("eq" => $mobile))
            ->load();
        if($customerCollection->getFirstItem())
            return $customerCollection->getFirstItem();
        else
            return false;
    }
}