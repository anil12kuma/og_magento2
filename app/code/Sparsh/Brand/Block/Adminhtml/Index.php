<?php
/**
 * Class Index
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Block\Adminhtml;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Index Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::__construct();
    }

    /**
     * Check user is allowed to access resource
     *
     * @param int $resourceId resourceId
     *
     * @return boolean
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
