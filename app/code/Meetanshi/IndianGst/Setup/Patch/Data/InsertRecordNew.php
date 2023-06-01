<?php

declare(strict_types=1);

namespace Meetanshi\IndianGst\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;


class InsertRecordNew implements DataPatchInterface
{
    const ENTITY_TYPE_CODE = 'catalog_product';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    private $attributeSetFactory;


    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return $this
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttributeGroup(
            self::ENTITY_TYPE_CODE,
            min($eavSetup->getAllAttributeSetIds('catalog_product')),
            'Meetanshi Shipping Type',
            150
        );

        $eavSetup->addAttribute(
            self::ENTITY_TYPE_CODE,
            'gst_rate',
            [
                'group' => 'GST India - Meetanshi',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'GST Rate (in Percentage)',
                'input' => 'select',
                'class' => '',
                'source' => 'Meetanshi\IndianGst\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'sort_order' => 5,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            self::ENTITY_TYPE_CODE,
            'min_gst_amount',
            [
                'group' => 'GST India - Meetanshi',
                'label' => 'Minimum Product Price to Apply Above GST Rate',
                'type'  => 'decimal',
                'input' => 'text',
                'required' => false,
                'sort_order' => 10,
                'searchable' => false,
                'filterable' => false,
                'length'    => '10,2',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            self::ENTITY_TYPE_CODE,
            'min_gst_rate',
            [
                'group' => 'GST India - Meetanshi',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'GST Rate to Apply on Products Below Minimum Set Price',
                'input' => 'select',
                'class' => '',
                'source' => 'Meetanshi\IndianGst\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'sort_order' => 15,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            self::ENTITY_TYPE_CODE,
            'hsn_code',
            [
                'group' => 'GST India - Meetanshi',
                'label' => 'Product HSN Code',
                'type'  => 'varchar',
                'input' => 'text',
                'required' => false,
                'sort_order' => 20,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'cat_gst_rate',
            [
                'group' => 'GST India - Meetanshi',
                'type' => 'varchar',
                'label' => 'GST Rate (in Percentage)',
                'input' => 'select',
                'class' => '',
                'source' => 'Meetanshi\IndianGst\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 5,
                'required' => false,
                'visible'      => true,
                'user_defined' => false,
                'default'      => null,
                'backend'      => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'cat_min_gst_amount',
            [
                'group' => 'GST India - Meetanshi',
                'label' => 'Minimum Product Price to Apply Above GST Rate',
                'type'  => 'decimal',
                'input' => 'text',
                'required' => false,
                'sort_order' => 10,
                'length'    => '10,2',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'cat_min_gst_rate',
            [
                'group' => 'GST India - Meetanshi',
                'type' => 'varchar',
                'label' => 'GST Rate to Apply on Products Below Minimum Set Price',
                'input' => 'select',
                'source' => 'Meetanshi\IndianGst\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 15,
                'group'        => '',
                'required' => false
            ]
        );

        $eavSetup = $this->eavSetupFactory->create();

        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $eavSetup->addAttribute('customer_address', 'buyer_gst_number', [
            'type'          => 'varchar',
            'label'         => 'Buyer GST Number',
            'input'         => 'text',
            'required'      =>  false,
            'visible'       =>  true,
            'user_defined'  =>  true,
            'group'            => 'General',
            'global'           => true,
            'sort_order'    =>  110,
            'position'      =>  110,
            'system'        =>  0
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer_address', 'buyer_gst_number');

        $customAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => [
                'adminhtml_customer_address',
                'customer_address_edit',
                'checkout_register',
                'customer_register_address'
            ],
        ]);
        $customAttribute->save();

        $this->moduleDataSetup->getConnection()->startSetup();
        $setup = $this->moduleDataSetup;
        $tableName = $this->moduleDataSetup->getTable('directory_country_region');
        $this->updateDirectoryCountry($setup, $tableName);
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function updateDirectoryCountry($setup, $tableName)
    {
        $tableNameRegion = "directory_country_region";
        $tableNameRegionName = "directory_country_region_name";

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $directoryconnection = $resource->getConnection();
        $directory_region = $resource->getTableName('directory_country_region');
        $sql = "SELECT * FROM " . $directory_region . " where country_id = 'IN'";
        $result = $directoryconnection->fetchAll($sql);

        if (count($result) == 0) {
            try{
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'JK', 'Jammu and Kashmir')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Jammu and Kashmir')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'HP', 'Himachal Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Himachal Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'PB', 'Punjab')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Punjab')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'CH', 'Chandigarh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Chandigarh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'UK', 'Uttarakhand')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Uttarakhand')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'HR', 'Haryana')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Haryana')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'DL', 'Delhi')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Delhi')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'RJ', 'Rajasthan')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
         ('en_US', LAST_INSERT_ID(), 'Rajasthan')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'UP', 'Uttar Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
         ('en_US', LAST_INSERT_ID(), 'Uttar Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'BR', 'Bihar')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
         ('en_US', LAST_INSERT_ID(), 'Bihar')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'SK', 'Sikkim')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Sikkim')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'AR', 'Arunachal Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Arunachal Pradesh')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'NL', 'Nagaland')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Nagaland')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'MN', 'Manipur')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Manipur')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'MZ', 'Mizoram')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Mizoram')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'TR', 'Tripura')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Tripura')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'ML', 'Meghlaya')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Meghlaya')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'AS', 'Assam')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Assam')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'WB', 'West Bengal')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'West Bengal'))");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'JH', 'Jharkhand')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Jharkhand')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'OR', 'Orissa')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Orissa')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'CG', 'Chhattisgarh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
         ('en_US', LAST_INSERT_ID(), 'Chhattisgarh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'MP', 'Madhya Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Madhya Pradesh')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'GJ', 'Gujarat')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        (('en_US', LAST_INSERT_ID(), 'Gujarat')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'DD', 'Daman and Diu')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Daman and Diu')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'DH', 'Dadra and Nagar Haveli')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Dadra and Nagar Haveli')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'MH', 'Maharashtra')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Maharashtra')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'KA', 'Karnataka')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Karnataka')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'GA', 'Goa')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Goa')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'LD', 'Lakshadweep')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Lakshadweep')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'KL', 'Kerala')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Kerala')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'TN', 'Tamil Nadu')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Tamil Nadu')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'PY', 'Pondicherry')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Pondicherry')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
        ('IN', 'AN', 'Andaman and Nicobar Islands')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Andaman and Nicobar Islands')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'TS', 'Telangana')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Telangana')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN', 'AP', 'Andhra Pradesh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        'en_US', LAST_INSERT_ID(), 'Andhra Pradesh')");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES
         ('IN','Leh','Ladakh')");
        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES
        ('en_US', LAST_INSERT_ID(), 'Ladakh')");}
            catch (\Exception $e) {
                }
        }

        try {
            $setup->getConnection()->query("ALTER INTO `{$tableNameRegion}`ADD `state_code` varchar(3) COMMENT 'State Code';");
            $setup->getConnection()->query("ALTER INTO `{$tableNameRegionName}`ADD `state_code` varchar(3) COMMENT 'State Code';");
        } catch (\Exception $e) {
        }

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '35' WHERE `default_name` = 'Andaman and Nicobar Islands';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '35' WHERE `name` = 'Andaman and Nicobar Islands';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '25' WHERE `default_name` = 'Daman and Diu';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '25' WHERE `name` = 'Daman and Diu';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '37' WHERE `default_name` = 'Andhra Pradesh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '37' WHERE `name` = 'Andhra Pradesh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '12' WHERE `default_name` = 
'Arunachal Pradesh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '12' WHERE `name` = 'Arunachal Pradesh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '18' WHERE `default_name` = 'Assam';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '18' WHERE `name` = 'Assam';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '10' WHERE `default_name` = 'Bihar';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '10' WHERE `name` = 'Bihar';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '04' WHERE `default_name` = 'Chandigarh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '04' WHERE `name` = 'Chandigarh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '22' WHERE `default_name` = 'Chhattisgarh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '22' WHERE `name` = 'Chhattisgarh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '26' WHERE `default_name` = 'Dadra and Nagar Haveli';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '26' WHERE `name` = 'Dadra and Nagar Haveli';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '07' WHERE `default_name` = 'Delhi';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '07' WHERE `name` = 'Delhi';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '30' WHERE `default_name` = 'Goa';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '30' WHERE `name` = 'Goa';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '24' WHERE `default_name` = 'Gujarat';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '24' WHERE `name` = 'Gujarat';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '06' WHERE `default_name` = 'Haryana';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '06' WHERE `name` = 'Haryana';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '02' WHERE `default_name` = 'Himachal Pradesh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '02' WHERE `name` = 'Himachal Pradesh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '01' WHERE `default_name` = 'Jammu and Kashmir';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '01' WHERE `name` = 'Jammu and Kashmir';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '20' WHERE `default_name` = 'Jharkhand';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '20' WHERE `name` = 'Jharkhand';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '29' WHERE `default_name` = 'Karnataka';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '29' WHERE `name` = 'Karnataka';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '32' WHERE `default_name` = 'Kerala';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '32' WHERE `name` = 'Kerala';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '31' WHERE `default_name` = 'Lakshadweep';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '31' WHERE `name` = 'Lakshadweep';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '23' WHERE `default_name` = 'Madhya Pradesh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '23' WHERE `name` = 'Madhya Pradesh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '27' WHERE `default_name` = 'Maharashtra';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '27' WHERE `name` = 'Maharashtra';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '14' WHERE `default_name` = 'Manipur';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '14' WHERE `name` = 'Manipur';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '17' WHERE `default_name` = 'Meghlaya';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '17' WHERE `name` = 'Meghlaya';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '15' WHERE `default_name` = 'Mizoram';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '15' WHERE `name` = 'Mizoram';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '13' WHERE `default_name` = 'Nagaland';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '13' WHERE `name` = 'Nagaland';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '21' WHERE `default_name` = 'Orissa';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '21' WHERE `name` = 'Orissa';");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegion}` (`country_id`, `code`, `default_name`) VALUES('IN', 'PY', 'Pondichery');");

        $setup->getConnection()->query("INSERT INTO `{$tableNameRegionName}` (`locale`, `region_id`, `name`) VALUES('en_US', LAST_INSERT_ID(), 'Pondichery');");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '34' WHERE `default_name` = 'Pondicherry';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '34' WHERE `name` = 'Pondicherry';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '03' WHERE `default_name` = 'Punjab';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '03' WHERE `name` = 'Punjab';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '08' WHERE `default_name` = 'Rajasthan';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '08' WHERE `name` = 'Rajasthan';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '11' WHERE `default_name` = 'Sikkim';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '11' WHERE `name` = 'Sikkim';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '33' WHERE `default_name` = 'Tamil Nadu';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '33' WHERE `name` = 'Tamil Nadu';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '36' WHERE `default_name` = 'Telangana';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '36' WHERE `name` = 'Telangana';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '16' WHERE `default_name` = 'Tripura';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '16' WHERE `name` = 'Tripura';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '09' WHERE `default_name` = 'Uttar Pradesh';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '09' WHERE `name` = 'Uttar Pradesh';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '05' WHERE `default_name` = 'Uttarakhand';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '05' WHERE `name` = 'Uttarakhand';");

        $setup->getConnection()->query("UPDATE `{$tableNameRegion}` SET `code` = '19' WHERE `default_name` = 'West Bengal';");
        $setup->getConnection()->query("UPDATE `{$tableNameRegionName}` SET `locale` = '19' WHERE `name` = 'West Bengal';");
    }
}
