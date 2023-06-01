<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\System\Config\Source;

/**
 * Model Status
 */
class Attributecode
{
    protected $_objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function toOptionArray()
    {
        $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->addVisibleFilter()
            ->addIsFilterableFilter()
            ->addFieldToFilter('main_table.frontend_input', ['eq' => 'select']);
        $array = [];
        $array[] = ['value' => '', 'label' => ''];
        foreach ($collection as $value) {
            $array[] = ['value' => $value->getAttributeCode(), 'label' => $value->getFrontendLabel()];
        }

        return $array;
    }
}
