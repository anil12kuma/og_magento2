<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2022 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\Ajaxlogin\Block\Messages\Logout;

use Magento\Framework\View\Element\Template;

/**
 * Class Error
 *
 * @package Tigren\Ajaxlogin\Block\Messages\Logout
 */
class Error extends Template
{
    /**
     * Error constructor.
     *
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(Template\Context $context, array $data)
    {
        parent::__construct($context, $data);
    }
}
