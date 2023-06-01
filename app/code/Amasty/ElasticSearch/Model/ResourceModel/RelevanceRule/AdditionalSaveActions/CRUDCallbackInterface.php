<?php

declare(strict_types=1);

namespace Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\AdditionalSaveActions;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;

interface CRUDCallbackInterface
{
    public function execute(RelevanceRuleInterface $rule): void;
}
