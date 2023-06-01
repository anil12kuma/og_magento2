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
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResponseFactory;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\Message\ManagerInterface;
use Mega\Phonelogin\Helper\Data ;

class CustomerCreatePostBefore implements ObserverInterface
{


    /*
     *
     */
    protected $userSession;

    /*
     *
     */
    protected  $responseFactory;

    /*
     *
     */
    protected  $urlInterface;

    /*
     *
     */
    protected  $messageManager;

    /*
     *
     */

    protected $megaHelper;

    public function __construct(
        Session $session, ResponseFactory $responseFactory, UrlInterface $urlInterface,
        ManagerInterface $messageInterface,
        Data $helper
        )
    {
        $this->userSession = $session;
        $this->responseFactory = $responseFactory;
        $this->urlInterface = $urlInterface;
        $this->messageManager = $messageInterface;
        $this->megaHelper = $helper;
    }

    /*
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this;
        $extensionEnabled =  $this->megaHelper->moduleIsEnabled();
        if($extensionEnabled){
            $isValid = $this->userSession->getData('mobile_verified');

            $redirectionUrl = $this->urlInterface->getUrl('customer/account/create');
            if(!$isValid){
                $this->userSession->setCustomerFormData($observer->getRequest()->getPostValue());
                $this->messageManager->addErrorMessage(__('Please Verify Your Mobile Number'));
                $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                die;
                return $this;
            }else{
                $observer->getRequest()->setParam('mphone_verified', 1);
            }
        }
    }
}