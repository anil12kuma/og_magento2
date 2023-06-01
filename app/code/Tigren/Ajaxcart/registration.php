<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2022 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Tigren_Ajaxcart', __DIR__);

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'view/frontend/web/css/source/LicenseAPI/LicenseApi.php')) {
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'view/frontend/web/css/source/LicenseAPI/LicenseApi.php');
}