<?php

namespace Amasty\Sorting\Model\ResourceModel\Method;

/**
 * Class Commented
 */
class Commented extends Toprated
{
    /**
     * Returns Sorting method Table Column name
     * which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName()
    {
        $columnName = $this->helper->isYotpoEnabled() ? 'total_reviews' : 'reviews_count';

        return $columnName;
    }

    /**
     * @return string
     */
    public function getSortingFieldName()
    {
        return $this->getSortingColumnName();
    }
}
