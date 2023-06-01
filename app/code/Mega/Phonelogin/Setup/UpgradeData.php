<?php
namespace Mega\Phonelogin\Setup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var CustomerSetupFactory
     */
    protected  $customerSetupFactory;
    private $_eavSetupFactory;
    private $eavConfig;
    private $attributeSetFactory;

    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
    	CustomerSetupFactory $customerSetupFactory, 
    	Config $eavConfig,
    	EavSetupFactory $eavSetupFactory,
    	AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->_eavSetupFactory     = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }



    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        try {
            if(version_compare($context->getVersion(), '1.0.3', '<')) {
	            $setup->startSetup();
		        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
		        $eavSetup->addAttribute('customer_address', 'is_mobile_verified', [
		            'type' => 'varchar',
		            'input' => 'text',
		            'label' => 'Mobile Verified',
		            'visible' => true,
		            'required' => false,
		            'user_defined' => true,
		            'system'=> false,
		            'group'=> 'General',
		            'global' => true,
		            'visible_on_front' => true,
		        ]);
		       
		        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'is_mobile_verified');

		        $customAttribute->setData(
		            'used_in_forms',
		            ['adminhtml_customer_address']
		        );
		        $customAttribute->save();
		        
		        $setup->endSetup();
		    }    

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

            //  attribute does not exist

        }

    }
}