<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\CancelOrder\Setup\Operation;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateTabsTable
     */
    private $createMainTable;

    public function __construct(
        Operation\CreateMainTable $createMainTable
    ) {
        $this->createMainTable = $createMainTable;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        $this->createMainTable->execute($setup);
        
        $setup->endSetup();
    }
}
