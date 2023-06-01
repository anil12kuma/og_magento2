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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /*
     *
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('mega_phonelogin_sms_log');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
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
                    'mobile_number',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'default' => ''
                    ],
                    'To Number'
                )
                ->addColumn(
                    'text',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'default' => ''
                    ],
                    'Text'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        'nullable' => false,
                        'default' => false
                    ],
                    'Message Sent'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ],
                    'Created At'
                );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}