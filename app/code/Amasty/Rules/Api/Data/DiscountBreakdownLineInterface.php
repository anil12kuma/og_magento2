<?php

namespace Amasty\Rules\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface DiscountBreakdownLineInterface extends ExtensibleDataInterface
{
    /**
     * Constant used as key into $_data
     */
    public const RULE_NAME = 'rule_name';
    public const RULE_AMOUNT = 'rule_amount';

    /**
     * @return string|null
     */
    public function getRuleName();

    /**
     * @param string $ruleName
     * @return $this
     */
    public function setRuleName($ruleName);

    /**
     * @return string
     */
    public function getRuleAmount();

    /**
     * @param string $ruleAmount
     * @return $this
     */
    public function setRuleAmount($ruleAmount);
}
