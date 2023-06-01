<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Setup\Operation;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateMainTable
{
    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    public function __construct(AppResource $resource)
    {
        $this->connection = $resource->getConnection('sales');
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->connection->createTable(
            $this->createMainTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createMainTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(CancelOrderInterface::TABLE_NAME);

        return $this->connection
            ->newTable(
                $table
            )->setComment(
                'Amasty Cancel Orders table'
            )->addColumn(
                CancelOrderInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Id'
            )->addColumn(
                CancelOrderInterface::ORDER_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Order Id'
            )->addColumn(
                CancelOrderInterface::REASON,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => ''
                ],
                'Cancel Reason'
            )->addColumn(
                CancelOrderInterface::COMMENT,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => ''
                ],
                'Comment'
            )->addColumn(
                CancelOrderInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ],
                'Status'
            )->addColumn(
                CancelOrderInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            );
    }
}
