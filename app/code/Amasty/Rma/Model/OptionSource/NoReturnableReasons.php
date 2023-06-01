<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Model\OptionSource;

class NoReturnableReasons
{
    const ALREADY_RETURNED = 0;
    const EXPIRED_PERIOD = 1;
    const REFUNDED = 2;
    const ITEM_WASNT_SHIPPED = 3;
    const ITEM_WAS_ON_SALE = 4;
}
