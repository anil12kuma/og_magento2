<?php

namespace Amasty\Rules\Api;

interface ExtendedValidatorInterface
{
    /**
     * @param $combineCondition
     * @param $type
     *
     * @return bool|null
     */
    public function validate($combineCondition, $type);
}
