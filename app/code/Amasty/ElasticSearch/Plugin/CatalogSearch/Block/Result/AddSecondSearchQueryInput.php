<?php

declare(strict_types=1);

namespace Amasty\ElasticSearch\Plugin\CatalogSearch\Block\Result;

use Amasty\ElasticSearch\Model\Config;
use Amasty\ElasticSearch\Model\Search\SubQuery\Helper as SubQueryHelper;
use Amasty\ElasticSearch\ViewModel\CatalogSearch\FurtherQueryViewModel;
use Magento\CatalogSearch\Block\Result as ResultBlock;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\Template;

class AddSecondSearchQueryInput
{
    private const TEMPLATE_NAME = 'Amasty_ElasticSearch::search/result/further_input.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var FurtherQueryViewModel
     */
    private $furtherQueryViewModel;

    /**
     * @var SubQueryHelper
     */
    private $subQueryHelper;

    public function __construct(
        Config $config,
        BlockFactory $blockFactory,
        FurtherQueryViewModel $furtherQueryViewModel,
        SubQueryHelper $subQueryHelper
    ) {
        $this->config = $config;
        $this->blockFactory = $blockFactory;
        $this->furtherQueryViewModel = $furtherQueryViewModel;
        $this->subQueryHelper = $subQueryHelper;
    }

    /**
     * Add further query block for search result block if allowed.
     *
     * @see ResultBlock::toHtml()
     */
    public function afterToHtml(ResultBlock $subject, string $result): string
    {
        if (!$subject->getResultCount() && $this->subQueryHelper->getQueryText() === null) {
            return $result;
        }

        if ($this->config->isFurtherQueryAllowed()) {
            $furtherBlock = $this->blockFactory->createBlock(Template::class, ['data' => [
                'template' => self::TEMPLATE_NAME,
                'furtherQueryViewModel' => $this->furtherQueryViewModel
            ]]);
            $result = $furtherBlock->toHtml() . $result;
        }

        return $result;
    }
}
