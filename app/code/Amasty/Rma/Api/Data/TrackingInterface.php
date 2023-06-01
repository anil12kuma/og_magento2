<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Api\Data;

/**
 * Interface TrackingInterface
 */
interface TrackingInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const TRACKING_ID = 'tracking_id';
    const REQUEST_ID = 'request_id';
    const TRACKING_CODE = 'tracking_code';
    const TRACKING_NUMBER = 'tracking_number';
    const IS_CUSTOMER = 'is_customer';
    /**#@-*/

    /**
     * @param int $trackingId
     *
     * @return \Amasty\Rma\Api\Data\TrackingInterface
     */
    public function setTrackingId($trackingId);

    /**
     * @return int
     */
    public function getTrackingId();

    /**
     * @param int $requestId
     *
     * @return \Amasty\Rma\Api\Data\TrackingInterface
     */
    public function setRequestId($requestId);

    /**
     * @return int
     */
    public function getRequestId();

    /**
     * @param string $trackingCode
     *
     * @return \Amasty\Rma\Api\Data\TrackingInterface
     */
    public function setTrackingCode($trackingCode);

    /**
     * @return string
     */
    public function getTrackingCode();

    /**
     * @param string $trackingNumber
     *
     * @return \Amasty\Rma\Api\Data\TrackingInterface
     */
    public function setTrackingNumber($trackingNumber);

    /**
     * @return string
     */
    public function getTrackingNumber();

    /**
     * @param bool $isCustomer
     *
     * @return \Amasty\Rma\Api\Data\TrackingInterface
     */
    public function setIsCustomer($isCustomer);

    /**
     * @return bool
     */
    public function isCustomer();
}
