<?php
declare(strict_types=1);

namespace Amasty\RulesPro\Setup;

use Amasty\RulesPro\Model\ResourceModel\RuleUsageCounter;
use Amasty\RulesPro\Model\ResourceModel\RuleUsageLimit;
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

        $installer->getConnection()->dropTable($installer->getTable(RuleUsageCounter::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable(RuleUsageLimit::TABLE_NAME));

        $installer->endSetup();
    }
}
