<?php

namespace Amasty\Rules\Api;

interface RuleProviderInterface
{
    /**
     * @param int $ruleId
     *
     * @return \Amasty\Rules\Model\Rule
     */
    public function getAmruleByRuleId($ruleId);
}
