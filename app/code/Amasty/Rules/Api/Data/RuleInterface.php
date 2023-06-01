<?php

namespace Amasty\Rules\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RuleInterface extends ExtensibleDataInterface
{
    public const RULE_NAME = 'amrules_rule';
    public const EXTENSION_CODE = 'amrules';

    /**
     * Constants defined for keys of data array
     */
    public const KEY_SALESRULE_ID = 'salesrule_id';
    public const KEY_PROMO_CATS = 'promo_cats';
    public const KEY_PROMO_SKUS = 'promo_skus';
    public const KEY_APPLY_DISCOUNT_TO = 'apply_discount_to';
    public const KEY_EACHM = 'eachm';
    public const KEY_PRICESELECTOR = 'priceselector';
    public const KEY_MAX_DISCOUNT = 'max_discount';
    public const KEY_NQTY = 'nqty';
    public const KEY_SKIP_RULE = 'skip_rule';

    /**
     * @return string|null
     */
    public function getPromoCats();

    /**
     * @param string $promoCats
     * @return $this
     */
    public function setPromoCats($promoCats);

    /**
     * @return string|null
     */
    public function getPromoSkus();

    /**
     * @param string $promoSkus
     * @return $this
     */
    public function setPromoSkus($promoSkus);

    /**
     * @return string|null
     */
    public function getApplyDiscountTo();

    /**
     * @param string $applyDiscountTo
     * @return $this
     */
    public function setApplyDiscountTo($applyDiscountTo);

    /**
     * @return string|null
     */
    public function getEachm();

    /**
     * @param string $eachm
     * @return $this
     */
    public function setEachm($eachm);

    /**
     * @return int|null
     */
    public function getPriceselector();

    /**
     * @param int $priceselector
     * @return $this
     */
    public function setPriceselector($priceselector);

    /**
     * @return string|null
     */
    public function getNqty();

    /**
     * @param string $nqty
     * @return $this
     */
    public function setNqty($nqty);

    /**
     * @return string|null
     */
    public function getMaxDiscount();

    /**
     * @param string $maxDiscount
     * @return $this
     */
    public function setMaxDiscount($maxDiscount);

    /**
     * @return int|null
     */
    public function getSkipRule();

    /**
     * @param int $skipRule
     * @return $this
     */
    public function setSkipRule($skipRule);
}
