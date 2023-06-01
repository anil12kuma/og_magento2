<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Model;

use Amasty\CancelOrder\Api\Data\CancelOrderInterface;
use Magento\Framework\Model\AbstractModel;

class CancelOrder extends AbstractModel implements CancelOrderInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CancelOrder\Model\ResourceModel\CancelOrder::class);
        $this->setIdFieldName(CancelOrderInterface::ID);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->_getData(CancelOrderInterface::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(CancelOrderInterface::ORDER_ID, $orderId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReason()
    {
        return $this->_getData(CancelOrderInterface::REASON);
    }

    /**
     * @inheritdoc
     */
    public function setReason($reason)
    {
        $this->setData(CancelOrderInterface::REASON, $reason);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->_getData(CancelOrderInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->setData(CancelOrderInterface::COMMENT, $comment);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(CancelOrderInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(CancelOrderInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(CancelOrderInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(CancelOrderInterface::CREATED_AT, $createdAt);

        return $this;
    }
}
