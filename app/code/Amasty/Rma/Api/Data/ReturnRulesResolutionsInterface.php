<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Api\Data;

interface ReturnRulesResolutionsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_RESOLUTION_ID = 'rule_resolution_id';
    const RULE_ID = 'rule_id';
    const RESOLUTION_ID = 'resolution_id';
    const VALUE = 'value';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\Rma\Api\Data\ReturnRulesResolutionsInterface
     */
    public function setRuleResolutionId($id);

    /**
     * @return int
     */
    public function getRuleResolutionId();

    /**
     * @param int $id
     *
     * @return \Amasty\Rma\Api\Data\ReturnRulesResolutionsInterface
     */
    public function setRuleId($id);

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int $id
     *
     * @return \Amasty\Rma\Api\Data\ReturnRulesResolutionsInterface
     */
    public function setResolutionId($id);

    /**
     * @return int
     */
    public function getResolutionId();

    /**
     * @param int|null $value
     *
     * @return \Amasty\Rma\Api\Data\ReturnRulesResolutionsInterface
     */
    public function setValue($value);

    /**
     * @return int|null
     */
    public function getValue();
}
