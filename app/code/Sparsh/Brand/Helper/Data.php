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
namespace Sparsh\Brand\Helper;

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
    protected $backendUrl;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context    context
     * @param \Magento\Backend\Model\UrlInterface   $backendUrl backendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        parent::__construct($context);
        $this->backendUrl = $backendUrl;
    }

    /**
     * Return Products Grid Url
     *
     * @return string
     */
    public function getProductsGridUrl()
    {
        return $this->backendUrl->getUrl(
            'brands/index/products',
            ['_current' => true]
        );
    }

    /**
     * Return config value of given path
     *
     * @param string $configPath configPath
     *
     * @return mixed
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
