<?php
/**
 * @Author      Magetop Team
 * @package     Review Management
 * @copyright   Copyright (c) 2019 MAGETOP (http://www.magetop.com)
 * @terms       http://www.magetop.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magetop\ReviewManagement\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        // Check if the table reviews_options already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('reviews_options')) != true) {
            // Create reviews_options table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('reviews_options')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'review_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Review Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Customer Id'
                ) 
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Type'
                )    
                ->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Content'
                )   
                ->addColumn(
                    'post_by',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Post By'
                )   
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )     
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )                            
                ->setComment('Reviews Options')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}