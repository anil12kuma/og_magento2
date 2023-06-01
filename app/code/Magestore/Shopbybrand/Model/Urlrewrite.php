<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

/**
 * Model Brand
 */
class Urlrewrite extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Model constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Shopbybrand\Model\ResourceModel\Urlrewrite');
    }

    public function loadByRequestPath($requestPath, $storeId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('request_path', $requestPath);
        if ($storeId) {
            $collection->addFieldToFilter('store_id', $storeId);
        }
        if ($collection->getSize()) {
            $model = $collection->getFirstItem();
            if ($model->getId()) {
                $this->load($model->getId());
            }
        }

        return $this;
    }
}
