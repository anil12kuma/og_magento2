<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Api\Data;

/**
 * Interface ReturnOrderItemInterface
 */
interface ReturnOrderItemInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ITEM = 'item';
    const PRODUCT_ITEM = 'product_item';
    const AVAILABLE_QTY = 'available_qty';
    const PURCHASED_QTY = 'purchased_qty';
    const IS_RETURNABLE = 'is_returnable';
    const NO_RETURNABLE_REASON = 'no_returnable_reason';
    const NO_RETURNABLE_DATA = 'no_returnable_data';
    const RESOLUTIONS = 'resolutions';
    /**#@-*/

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setItem($item);

    /**
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function getItem();

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|bool $productItem
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setProductItem($productItem);

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|bool
     */
    public function getProductItem();

    /**
     * @param double $qty
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setAvailableQty($qty);

    /**
     * @return double
     */
    public function getAvailableQty();

    /**
     * @param double $qty
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setPurchasedQty($qty);

    /**
     * @return double
     */
    public function getPurchasedQty();

    /**
     * @param bool $isReturnable
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setIsReturnable($isReturnable);
    /**
     * @return bool
     */
    public function isReturnable();

    /**
     * @param int $reason
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setNoReturnableReason($reason);

    /**
     * @return int
     */
    public function getNoReturnableReason();

    /**
     * @param array $data
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setNoReturnableData($data);

    /**
     * @return array
     */
    public function getNoReturnableData();

    /**
     * @param \Amasty\Rma\Api\Data\ResolutionInterface[] $resolutions
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderItemInterface
     */
    public function setResolutions($resolutions);

    /**
     * @return \Amasty\Rma\Api\Data\ResolutionInterface[]
     */
    public function getResolutions();
}
