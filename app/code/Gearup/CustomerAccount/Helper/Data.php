<?php
/**
 * Class Data
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Gearup\CustomerAccount\Helper;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * BackendUrlInterface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $orderRepository;

    protected $productFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context    context
     * @param \Magento\Backend\Model\UrlInterface   $backendUrl backendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * Return Products Grid Url
     *
     * @return string
     */
    public function getOrderInfo($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    public function getProductInfo($productId)
    {
        return $this->productFactory->create()->load($productId);
    }
}
