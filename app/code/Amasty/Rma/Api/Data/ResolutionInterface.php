<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Api\Data;

/**
 * Interface ResolutionInterface
 */
interface ResolutionInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RESOLUTION_ID = 'resolution_id';
    const TITLE = 'title';
    const STATUS = 'status';
    const POSITION = 'position';
    const STORES = 'stores';
    const LABEL = 'label';
    const IS_DELETED = 'is_deleted';
    /**#@-*/

    /**
     * @param int $resolutionId
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setResolutionId($resolutionId);

    /**
     * @return int
     */
    public function getResolutionId();

    /**
     * @param string $title
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param int $status
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $position
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param \Amasty\Rma\Api\Data\ResolutionStoreInterface[]
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setStores($stores);

    /**
     * @return \Amasty\Rma\Api\Data\ResolutionStoreInterface[]
     */
    public function getStores();

    /**
     * @param string $label
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param bool $isDeleted
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface
     */
    public function setIsDeleted($isDeleted);

    /**
     * @return bool
     */
    public function getIsDeleted();
}
