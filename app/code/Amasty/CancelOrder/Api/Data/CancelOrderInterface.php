<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Api\Data;

interface CancelOrderInterface
{
    const TABLE_NAME = 'amasty_cancel_order';

    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const REASON = 'reason';
    const COMMENT = 'comment';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    /**#@-*/

    const ORDER_CREATED_AT = 'order_created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @return string|null
     */
    public function getReason();

    /**
     * @param string|null $reason
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setReason($reason);

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string|null $comment
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setComment($comment);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function setCreatedAt($createdAt);
}
