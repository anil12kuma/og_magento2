<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Setup;

use Magento\Eav\Setup\EavSetupFactory;
//use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
//use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shop By Brand
 * @author   Magestore Developer
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Schema table.
     */
    const SCHEMA_BRAND = 'ms_brand';
    const SCHEMA_BRAND_PRODUCT = 'ms_brand_products';
    const SCHEMA_BRAND_STORE_VALUE = 'ms_brand_store_value';
    const SCHEMA_BRAND_CATALOG = 'ms_brand_categories';
//    const SCHEMA_BRAND_FLAT = 'brand_flat';
    const SCHEMA_STORE = 'store';
//    const SCHEMA_BRAND_SUBSCRIBER = 'brand_subscriber';
//    const SCHEMA_NEWSLETTER_SUBSCRIBER = 'newsletter_subscriber';
    //const SCHEMA_MANUFACTURER = 'manufacturer';
//    protected $_objectManager;
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $_moduleDataSetupInterface;
    /**
     * EAV product attribute factory
     *
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetupInterface,
        AttributeFactory $attributeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->_attributeFactory = $attributeFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /* delete old attribute option - Ronald */
        if ($installer->getConnection()->isTableExists($installer->getTable(self::SCHEMA_BRAND))) {
	        $select_brand = $installer->getConnection()->select()->from(self::SCHEMA_BRAND);
	        $old_brand_data = $installer->getConnection()->fetchAll($select_brand);
	        if(!empty($old_brand_data)) {
	            $attributeCode = $this->scopeConfig->getValue('ms_shopbybrand/general/attribute_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	            $attributeCode = $attributeCode ? $attributeCode : 'manufacturer';
	            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
	            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->_moduleDataSetupInterface]);
	            $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
	            $attribute = $this->_attributeFactory->create()->loadByCode($entityTypeId, $attributeCode);
	            $options = $attribute->getOptions();
	            $optionsToRemove = [];
	            foreach ($old_brand_data as $brand){
	                if(isset($brand['brand_name']) && $brand['brand_name'] != ''){
	                    foreach($options as $option)
	                    {
	                        if ($option['value'] && strcasecmp($brand['brand_name'], $option['label']) == 0)
	                        {
	                            $optionsToRemove['delete'][$option['value']] = true;
	                            $optionsToRemove['value'][$option['value']] = true;
	                        }
	                    }
	                }
	            }
	            if(sizeof($optionsToRemove) > 0)
	                $eavSetup->addAttributeOption($optionsToRemove);
	        }
	    }
        /* end delete old attribute option - Ronald */

        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BRAND));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BRAND_PRODUCT));
//        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BRAND_CATALOG));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BRAND_STORE_VALUE));
//        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BRAND_SUBSCRIBER));

        /*
         * Create table brand
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_BRAND))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Brand Id'
            )->addColumn(
                'brand_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Brand Name'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Page Title'
            )->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Url Key'
            )->addColumn(
                'logo',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Logo'
            )->addColumn(
                'banner',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Banner'
            )->addColumn(
                'banner_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Banner URL'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Description'
            )->addColumn(
                'short_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Short Description'
            )->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Option Id'
            )->addColumn(
                'meta_keywords',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Meta Keywords'
            )->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Meta Descriptions'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )->addColumn(
                'is_featured',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => '2'],
                'Is Featured'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => '1'],
                'Status'
            )->addColumn(
                'position_brand',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'unsigned' => true],
                'Position Brand'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Sort Order'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_BRAND, ['id', 'brand_name', 'title']),
                ['id', 'brand_name', 'title']
            )->setComment('SCHEMA_BRAND');

        $installer->getConnection()->createTable($table);

        /*
        * Create table brand_products
        */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_BRAND_PRODUCT))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Brand Product Id'
            )->addColumn(
                'brand_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Brand Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Product Id'
            )->addColumn(
                'is_featured',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => '0'],
                'Is Featured'
            )->addColumn(
                'visibility_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => '1'],
                'visibility status'
            )->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Position'
            )->addColumn(
                'category_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Category Ids'
            );

        $installer->getConnection()->createTable($table);

        /*
        * Create table brand_products
        */
//        $table = $installer->getConnection()
//            ->newTable($installer->getTable(self::SCHEMA_BRAND_CATALOG))
//            ->addColumn(
//                'id',
//                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
//                null,
//                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
//                'Brand Product Id'
//            )->addColumn(
//                'brand_id',
//                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
//                11,
//                ['unsigned' => true, 'nullable' => true],
//                'Brand Id'
//            )->addColumn(
//                'category_id',
//                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
//                null,
//                ['nullable' => true, 'default' => ''],
//                'Category Id'
//            );

        //$installer->getConnection()->createTable($table);

        /*
        * Create table brand_store_value
        */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_BRAND_STORE_VALUE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )->addColumn(
                'brand_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => true],
                'Brand Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Store ID'
            )->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Code'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Value'
            )->addForeignKey(
                $installer->getFkName(
                    self::SCHEMA_BRAND_STORE_VALUE,
                    'brand_id',
                    self::SCHEMA_BRAND,
                    'id'
                ),
                'brand_id',
                $installer->getTable(self::SCHEMA_BRAND),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )->addForeignKey(
                $installer->getFkName(
                    self::SCHEMA_BRAND_STORE_VALUE,
                    'store_id',
                    self::SCHEMA_STORE,
                    'store_id'
                ),
                'store_id',
                $installer->getTable(self::SCHEMA_STORE),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            );

        $installer->getConnection()->createTable($table);
        /*
        * Create table brand_subscriber
        */
/*        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::SCHEMA_BRAND_SUBSCRIBER)
        )->addColumn(
            'brand_subscriber_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Brand Subscriber Id'
        )->addColumn(
            'brand_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => true],
            'Brand Id'
        )->addColumn(
            'subscriber_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => true, 'default' => '0'],
            'Subscriber Id'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::SCHEMA_BRAND_STORE_VALUE),
                ['brand_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['brand_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable(self::SCHEMA_BRAND_STORE_VALUE),
                ['subscriber_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['subscriber_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                self::SCHEMA_BRAND_SUBSCRIBER,
                'brand_id',
                self::SCHEMA_BRAND,
                'brand_id'
            ),
            'brand_id',
            $installer->getTable(self::SCHEMA_BRAND),
            'brand_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                self::SCHEMA_BRAND_STORE_VALUE,
                'subscriber_id',
                self::SCHEMA_NEWSLETTER_SUBSCRIBER,
                'subscriber_id'
            ),
            'subscriber_id',
            $installer->getTable(self::SCHEMA_NEWSLETTER_SUBSCRIBER),
            'subscriber_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );

        $installer->getConnection()->createTable($table);
*/


        /*update data*/
//        if ($installer->tableExists($installer->getTable(self::SCHEMA_MANUFACTURER))) {
//            $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand')->convertData();
//        } else {
//            $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand')->updateBrandsFormCatalog();
//        }


        /*
         * Create table brand
         */
/*        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::SCHEMA_BRAND_FLAT)
        )->addColumn(
            'brand_flat_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Brand Flat Id'
        )->addColumn(
            'brand_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false],
            'Brand Id'
//        )->addColumn(
//            'category_id',
//            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
//            255,
//            ['nullable' => false],
//            'Category Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Id'
        )->addColumn(
            'product_isvisible',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product is Visible'

        )->addColumn(
            'is_featured',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'Is Featured'
        );

        $installer->getConnection()->createTable($table);*/

        $installer->endSetup();

    }
}
