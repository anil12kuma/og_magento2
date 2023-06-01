<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


declare(strict_types=1);

namespace Amasty\CancelOrder\Ui\DataProvider\Listing;

use Magento\Framework\Api\Filter;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array
     */
    private $mappedFields = [
        'created_at' => 'main_table.created_at',
        'status' => 'main_table.status'
    ];

    /**
     * @param Filter $filter
     * @return mixed|void
     */
    public function addFilter(Filter $filter)
    {
        if (array_key_exists($filter->getField(), $this->mappedFields)) {
            $mappedField = $this->mappedFields[$filter->getField()];
            $filter->setField($mappedField);
        }

        parent::addFilter($filter);
    }
}
