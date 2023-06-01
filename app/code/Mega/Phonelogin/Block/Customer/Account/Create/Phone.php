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

namespace Mega\Phonelogin\Block\Customer\Account\Create;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Phone extends \Magento\Customer\Block\Form\Edit
{

    protected $helper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Mega\Phonelogin\Helper\Data $helper,
        array $data = [])
    {

        $this->helper = $helper;
        parent::__construct($context, $customerSession, $subscriberFactory, $customerRepository, $customerAccountManagement, $data);
    }



    public function getMphoneNumber(){
        $email = $this->getCustomer()->getEmail();
        $customer = $this->helper->getUserFromPhone($email);
        return $customer->getMphoneNumber();
    }


    public function isExtensionEnabled()
    {
        return $this->helper->moduleIsEnabled();

    }

}