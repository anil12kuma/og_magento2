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
namespace Sparsh\Brand\Block\Adminhtml\Index\Edit\Tab;

use Magento\Backend\Block\Template\Context;

/**
 * Class Products
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * ProductCollectionFactory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * BrandFactory
     *
     * @var \Sparsh\Brand\Model\BrandFactory
     */
    protected $brandFactory;

    /**
     * Products constructor.
     *
     * @param Context                                                        $context           context
     * @param \Magento\Backend\Helper\Data                                   $backendHelper     backendHelper
     * @param \Sparsh\Brand\Model\BrandFactory                           $brandFactory      brandFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection productCollection
     * @param array                                                          $data              data
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Sparsh\Brand\Model\BrandFactory $brandFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        array $data = []
    ) {
        $this->brandFactory = $brandFactory;
        $this->productCollection = $productCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('brand_product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('brand_id')) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * Add column filter to the collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column column
     *
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_product') {
            $brandProductIds = $this->_getSelectedProducts();

            if (empty($brandProductIds)) {
                $brandProductIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()
                    ->addFieldToFilter('entity_id', ['in' => $brandProductIds]);
            } else {
                if ($brandProductIds) {
                    $this->getCollection()
                        ->addFieldToFilter('entity_id', ['nin' => $brandProductIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare Collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     *
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->_getSelectedProducts(),
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * Get Row Url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getSelectedProducts();
        return $products;
    }
 
    /**
     * Get selected products
     *
     * @return mixed
     * this function get product Id that you checked
     */
    public function getSelectedProducts()
    {
        $brand = $this->getBrand();
        $brandProducts = $brand->getProducts($brand);

        if (!is_array($brandProducts)) {
            $brandProducts = [];
        }

        return $brandProducts;
    }

    /**
     * Get current Brand
     *
     * @return \Sparsh\Brand\Model\Brand
     */
    protected function getBrand()
    {
        $brandId = $this->getRequest()->getParam('brand_id');
        $brand   = $this->brandFactory->create();
        if ($brandId) {
            $brand->load($brandId);
        }

        return $brand;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isHidden()
    {
        return true;
    }
}
