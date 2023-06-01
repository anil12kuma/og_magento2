<?php
declare(strict_types=1);

namespace Amasty\Rules\Setup;

use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $installer, ModuleContextInterface $context): void
    {
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable(Rule::TABLE_NAME));

        $installer->endSetup();
    }
}
