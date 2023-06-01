<?php
/**
 * Class Products
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class Products
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Products extends \Magento\Backend\App\Action
{

    /**
     * ResultLayoutFactory
     *
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Products constructor.
     *
     * @param Action\Context $context             context
     * @param LayoutFactory  $resultLayoutFactory resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Products Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()
            ->getBlock('brand.edit.tab.products')
            ->setInProducts(
                $this->getRequest()->getPost('brand_products', null)
            );

        return $resultLayout;
    }
}
