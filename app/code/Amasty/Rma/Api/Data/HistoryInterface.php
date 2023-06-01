<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Api\Data;

interface HistoryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const EVENT_ID = 'event_id';
    const REQUEST_ID = 'request_id';
    const EVENT_DATE = 'event_date';
    const EVENT_TYPE = 'event_type';
    const EVENT_DATA = 'event_data';
    const EVENT_INITIATOR = 'event_initiator';
    const EVENT_INITIATOR_NAME = 'event_initiator_name';
    const MESSAGE = 'message';
    /**#@-*/

    /**
     * @return int
     */
    public function getEventId();

    /**
     * @param int $eventId
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setEventId($eventId);

    /**
     * @return int
     */
    public function getRequestId();

    /**
     * @param int $requestId
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setRequestId($requestId);

    /**
     * @return string
     */
    public function getEventDate();

    /**
     * @return int
     */
    public function getEventType();

    /**
     * @param int $eventType
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setEventType($eventType);

    /**
     * @return array
     */
    public function getEventData();

    /**
     * @param array $data
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setEventData($data);

    /**
     * @return int
     */
    public function getEventInitiator();

    /**
     * @param int $initiator
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setEventInitiator($initiator);

    /**
     * @return string
     */
    public function getEventInitiatorName();

    /**
     * @param string $initiatorName
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setEventInitiatorName($initiatorName);

    /**
     * @param string $message
     *
     * @return \Amasty\Rma\Api\Data\HistoryInterface
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getMessage();
}
