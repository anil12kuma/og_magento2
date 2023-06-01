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

namespace Mega\Phonelogin\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected  $customerSetupFactory;

    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
        $customerSetup->addAttribute($customerEntityTypeId, "mphone_number", [
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Mobile Number",
            "input"    => "text",
            "visible"  => true,
            "default" => "",
            "frontend" => "",
            "unique"     => true,
            "note"       => "Mobile Number used for customer account management, in format +<country_code><mobile number>"
        ]);
        $usedInForms[] = "adminhtml_customer";
        $usedInForms[] = "checkout_register";
        $usedInForms[] = "customer_account_create";
        $usedInForms[] = "customer_account_edit";
        $usedInForms[] = "adminhtml_checkout";

        $customerPhoneAttribute = $customerSetup->getEavConfig()->getAttribute($customerEntityTypeId, 'mphone_number');
        $customerPhoneAttribute->setData("used_in_forms", $usedInForms)
                            ->setData("is_used_for_customer_segment", true)
                            ->setData("is_system", 0)
                            ->setData("is_user_defined", 1)
                            ->setData('is_required',0)
                            ->setData("is_visible", 1)
                            ->setData("sort_order", 100);
                            //->setData('is_used_in_grid',1);
        $customerPhoneAttribute->save();

    }
}