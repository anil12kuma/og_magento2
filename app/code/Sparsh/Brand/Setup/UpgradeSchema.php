<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sparsh\Brand\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
                $table = $setup->getConnection()->newTable(
                $setup->getTable('sparsh_brand_store')
            )->addColumn(
                'brand_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Brand ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex($setup->getIdxName('sparsh_brand_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $setup->getFkName('sparsh_brand_store', 'brand_id', 'sparsh_brand', 'brand_id'),'brand_id',
                $setup->getTable('sparsh_brand'),'brand_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('sparsh_brand_store', 'store_id', 'store', 'store_id'),'store_id',
                $setup->getTable('store'),'store_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Sparsh Brand Stores Table'
            );
            $setup->getConnection()->createTable($table);

            $Customertable = $setup->getConnection()->newTable(
                $setup->getTable('sparsh_brand_customer_group')
            )->addColumn(
                'brand_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Brand ID'
            )->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group ID'
            )->addIndex($setup->getIdxName('sparsh_brand_customer_group', ['customer_group_id']),
                ['customer_group_id']
            )->addForeignKey(
                $setup->getFkName('sparsh_brand_customer_group', 'brand_id', 'sparsh_brand', 'brand_id'),'brand_id',
                $setup->getTable('sparsh_brand'),'brand_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('sparsh_brand_customer_group', 'customer_group_id', 'customer_group', 'customer_group_id'),'customer_group_id',
                $setup->getTable('customer_group'),'customer_group_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Sparsh Brand Customer Groups Table'
            );
            $setup->getConnection()->createTable($Customertable);
        }
        $setup->endSetup();
    }
}
