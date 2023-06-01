<?php
namespace Mageplaza\Shopbycate\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}

	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(
				\Magento\Catalog\Model\Category::ENTITY,
			'shop_by_cate',
			[
				'type' => 'int',
				'label' => 'Shop by category',
				'input' => 'boolean',
				'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				'global' => 1,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => 0,
				'group' => '',
				'backend' => ''
			]
		);
	}
}