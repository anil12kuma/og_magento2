<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to samparker801@gmail.com so we can send you a copy immediately.
 *
 * @category    Mega
 * @package     Mega_PhoneLogin
 * @copyright   Copyright (c) 2017 Sam Parker (samparker801@gmail.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mega\Phonelogin\Model\Api;


class Providers implements \Magento\Framework\Option\ArrayInterface
{
    const API_SPRINGEDGE = 0;
    const API_TWILIO = 1;
    const API_ROUTEE = 2;
    const API_MSG91 = 3;
    const API_SERWER = 4;
    const API_BULK_SMS = 5;
    const WHISTLE_MOBI = 6;


    public function toOptionArray()
    {
        return [
            ['value' => self::API_SPRINGEDGE, 'label' => __('Spring SMS API')],
            ['value' => self::API_ROUTEE, 'label' => __('Routee SMS API')],
            ['value' => self::API_MSG91, 'label' => __('Msg91 SMS API')],
            ['value' => self::API_TWILIO , 'label' => __('Twilio SMS API')],
            ['value' => self::API_SERWER , 'label' => __('Serwer SMS API')],
            ['value' => self::API_BULK_SMS , 'label' => __('Bulk Sms API')],
            ['value' => self::WHISTLE_MOBI , 'label' => __('Whistle Mobi')]
        ];

    }
}