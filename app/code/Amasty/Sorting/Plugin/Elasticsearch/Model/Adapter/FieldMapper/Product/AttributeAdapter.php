<?php

declare(strict_types=1);

namespace Amasty\Sorting\Plugin\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Amasty\Sorting\Model\Method\GetAttributeCodesForSorting;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter as NativeAttributeAdapter;

class AttributeAdapter
{
    /**
     * @var GetAttributeCodesForSorting
     */
    private $getAttributeCodesForSorting;

    public function __construct(GetAttributeCodesForSorting $getAttributeCodesForSorting)
    {
        $this->getAttributeCodesForSorting = $getAttributeCodesForSorting;
    }

    /**
     * @param NativeAttributeAdapter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsSortable($subject, $result)
    {
        if (in_array($subject->getAttributeCode(), $this->getAttributeCodesForSorting->execute())) {
            $result = true;
        }

        return $result;
    }
}
