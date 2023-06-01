<?php

declare(strict_types=1);

namespace Amasty\SortingGraphQl\Plugin\Sorting\Model\MethodProvider;

use Amasty\Sorting\Model\MethodProvider;
use Amasty\SortingGraphQl\Model\MethodProvider\CodeMap;

class FixMethodCode
{
    /**
     * @var CodeMap
     */
    private $codeMap;

    public function __construct(CodeMap $codeMap)
    {
        $this->codeMap = $codeMap;
    }

    /**
     * Need in case when we replace order code in criteria for correct elastic search.
     *
     * @see MethodProvider::getMethodByCode
     *
     * @param MethodProvider $subject
     * @param string $code
     * @return array
     */
    public function beforeGetMethodByCode(MethodProvider $subject, string $code): array
    {
        if ($originalCode = $this->codeMap->get($code)) {
            $code = $originalCode;
        }

        return [$code];
    }
}
