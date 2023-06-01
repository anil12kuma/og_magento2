<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Observer;

class RmaEventNames
{
    const STATUS_CHANGED = 'amasty_rma_status_changed';
    const STATUS_AUTOMATICALLY_CHANGED = 'amasty_rma_status_automatically_changed';

    //Customer Events
    const NEW_CHAT_MESSAGE_BY_CUSTOMER = 'amasty_customer_rma_new_message';
    const CHAT_MESSAGE_DELETED_BY_CUSTOMER = 'amasty_customer_rma_deleted_message';
    const BEFORE_CREATE_RMA_BY_CUSTOMER = 'amasty_customer_rma_before_create';
    const RMA_CREATED_BY_CUSTOMER = 'amasty_customer_rma_created';
    const RMA_RATED = 'amasty_customer_rated_rma';
    const TRACKING_NUMBER_ADDED_BY_CUSTOMER = 'amasty_customer_added_tracking_number_rma';
    const TRACKING_NUMBER_DELETED_BY_CUSTOMER = 'amasty_customer_deleted_tracking_number_rma';
    const RMA_CANCELED = 'amasty_customer_rma_canceled';
    //Admin Events
    const NEW_CHAT_MESSAGE_BY_MANAGER = 'amasty_manager_rma_new_message';
    const CHAT_MESSAGE_DELETED_BY_MANAGER = 'amasty_manager_rma_deleted_message';
    const BEFORE_CREATE_RMA_BY_MANAGER = 'amasty_manager_rma_before_create';
    const RMA_CREATED_BY_MANAGER = 'amasty_manager_rma_created';
    const TRACKING_NUMBER_ADDED_BY_MANAGER = 'amasty_manager_added_tracking_number_rma';
    const TRACKING_NUMBER_DELETED_BY_MANAGER = 'amasty_manager_deleted_tracking_number_rma';
    const SHIPPING_LABEL_ADDED_BY_MANAGER = 'amasty_manager_added_shipping_label_rma';
    const SHIPPING_LABEL_DELETED_BY_MANAGER = 'amasty_manager_deleted_shipping_label_rma';
    const RMA_SAVED_BY_MANAGER = 'amasty_manager_rma_saved';
    //System Events
    const STATUS_CHANGED_BY_SYSTEM = 'amasty_rma_system_status_changed';
    const MANAGER_CHANGED_BY_SYSTEM = 'amasty_rma_system_manager_changed';
}
