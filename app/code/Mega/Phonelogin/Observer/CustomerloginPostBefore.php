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

use Magento\Framework\Event\ObserverInterface;
class CustomerloginPostBefore implements ObserverInterface
{
    protected  $_objectManager;

    public function __construct( \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function  execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getRequest();
        $userName = $request->getParam('login')['username'];
        if(!$userName)
            return;

        $helper = $this->_objectManager->create('Mega\Phonelogin\Helper\Data');
        $validateEmail = $helper->isEmail($userName);
        
        if(!$validateEmail){

            /*
             * @todo add code to fetch phone number using email
             * */

        }
        $request->setParam('login["username"]','mlolp@mail.com');
        /*var_dump($userName);
        print_r($request->getParams());
        die('exce');*/

    }
}