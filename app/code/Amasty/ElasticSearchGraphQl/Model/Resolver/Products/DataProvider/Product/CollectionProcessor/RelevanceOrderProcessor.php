<?php

declare(strict_types=1);

namespace Amasty\ElasticSearchGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\IndexBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\GraphQl\Model\Query\ContextInterface;
use Zend_Db_Expr as Zend_Db_Expr;

class RelevanceOrderProcessor implements CollectionProcessorInterface
{
    public const RELEVANCE_ORDER_COLUMN = 'search_result.score';
    public const AMASTY_RELEVANCE_ORDER_COLUMN = 'amasty_relevance_order';
    public const AMASTY_RELEVANCE_TABLE_ALIAS = 'amasty_relevance_index';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        if ($context !== null && $context->getExtensionAttributes()->getStore() !== null) {
            $select = $collection->getSelect();
            $selectOrders = $select->getPart(Select::ORDER);

            if ($this->isRelevanceOrder($selectOrders)) {
                $websiteId = (int) $context->getExtensionAttributes()->getStore()->getWebsiteId();
                $this->joinRelevanceIndex($select, $websiteId);
                array_unshift(
                    $selectOrders,
                    [self::AMASTY_RELEVANCE_ORDER_COLUMN, $this->getRelevanceDirection($selectOrders)]
                );
                $select->reset(Select::ORDER);
                $select->setPart(Select::ORDER, $selectOrders);
            }
        }

        return $collection;
    }

    private function isRelevanceOrder(array $selectOrders): bool
    {
        $firstOrder = reset($selectOrders);
        $orderColumn = $firstOrder[0] ?? '';

        return $orderColumn === self::RELEVANCE_ORDER_COLUMN;
    }

    private function getRelevanceDirection(array $selectOrders): string
    {
        $firstOrder = reset($selectOrders);

        return $firstOrder[1] ?? Select::SQL_DESC;
    }

    private function joinRelevanceIndex(Select $select, int $websiteId): void
    {
        $select->joinLeft(
            [
                self::AMASTY_RELEVANCE_TABLE_ALIAS => $this->resourceConnection->getTableName(IndexBuilder::TABLE_NAME)
            ],
            sprintf(
                'e.entity_id = %1$s.%2$s and %1$s.%3$s = %4$d',
                self::AMASTY_RELEVANCE_TABLE_ALIAS,
                IndexBuilder::PRODUCT_ID,
                RelevanceRuleInterface::WEBSITE_ID,
                $websiteId
            ),
            [self::AMASTY_RELEVANCE_ORDER_COLUMN => $this->getRelevanceExpression()]
        );
        $select->group('e.entity_id');
    }

    private function getRelevanceExpression(): Zend_Db_Expr
    {
        $relevanceExpression = sprintf(
            'GREATEST(SUM(GREATEST(%1$s.multiplier, 0)), 1) / GREATEST(ABS(SUM(LEAST((%1$s.multiplier), 0)))',
            self::AMASTY_RELEVANCE_TABLE_ALIAS
        );

        return new Zend_Db_Expr(sprintf('IFNULL(%s, 1), 1)', $relevanceExpression));
    }
}
