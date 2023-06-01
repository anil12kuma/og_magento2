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

namespace Mega\Phonelogin\Observer;


use Mega\Phonelogin\Model\SmsLogFactory;

class SendOtpAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected  $_smsLogFactory;

    /*
     *
     */
    protected $_logger;

    /*
     *
     */
    public function __construct(
        SmsLogFactory $smsLogFactory,
        \Psr\Log\LoggerInterface $logger)
    {
        $this->_smsLogFactory = $smsLogFactory;
        $this->_logger = $logger;
        return $this;
    }


    /*
     *
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $logData = $observer->getEvent()->getEventData();
            if( !isset( $logData['to']) ||
                !isset($logData['message']) || !isset($logData['message'])){
                $this->_logger->debug('returnin');
                $this->_logger->debug(json_encode($logData));
                return $this;
            }
            $logModel = $this->_smsLogFactory->create();
            $logModel->setData('mobile_number', $logData['to']);
            $logModel->setData('text', $logData['message']);
            if($logData['status']){
                $logModel->setData('status',1);
            }else{
                $logModel->setData('status',0);
            }

            $logModel->save();
            $this->_logger->debug($logData['to'].'=='.$logData['message']);

        }catch (\Exception $e){
            echo $e->getMessage();
        }

        return $this;
    }
}
