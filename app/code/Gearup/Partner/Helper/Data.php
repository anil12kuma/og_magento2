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
namespace Gearup\Partner\Helper;

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
    protected $brandModel;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context    context
     * @param \Magento\Backend\Model\UrlInterface   $backendUrl backendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Sparsh\Brand\Model\Brand $brandModel
    ) {
        parent::__construct($context);
        $this->brandModel = $brandModel;
    }

    /**
     * Return Products Grid Url
     *
     * @return string
     */
    public function getBrandName($productId)
    {
        $collection = $this->brandModel->getCollection();
        $collection->getSelect()->join('sparsh_brand_products as sbp','main_table.brand_id = sbp.brand_id', array('main_table.title','main_table.url_key'))->where("sbp.product_id = ".$productId);
        $collection->getSelect()->limit(1);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['main_table.title','main_table.url_key']);
        $brandData = array();
        foreach ($collection as $brand) {
            $brandData['brandName'] = $brand->getTitle();
            $brandData['brandUrlKey'] = $brand->getUrlKey();
        }
        return $brandData;
    }
}
