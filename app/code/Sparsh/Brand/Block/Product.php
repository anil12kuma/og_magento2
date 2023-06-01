<?php
/**
 * Class Product
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Block;

use Magento\Framework\App\Action\Action;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Product
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Product extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * ProductCollectionFactory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ListProduct Block
     *
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $listProductBlock;

    /**
     * ReviewFactory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * BrandModel
     *
     * @var \Sparsh\Brand\Model\Brand
     */
    protected $brand;

    /**
     * BrandProductModel
     *
     * @var \Sparsh\Brand\Model\BrandProduct
     */
    protected $brandProduct;

    /**
     * BrandHelper
     *
     * @var \Sparsh\Brand\Helper\Data
     */
    protected $brandhelper;

    /**
     * ProductVisibilityModel
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * UrlRewriteFactory
     *
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * ImageFactory
     *
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * ResultPageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * FileSystem
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;
	
	private $layoutFactory;
	protected $context;

    /**
     * Product constructor.
     *
     * @param Context                                                        $context                  context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager             storeManager
     * @param \Magento\Catalog\Block\Product\ListProduct                     $listProductBlock         listProductBlock
     * @param \Magento\Review\Model\ReviewFactory                            $reviewFactory            reviewFactory
     * @param \Sparsh\Brand\Model\Brand                                  $brand                    brand
     * @param \Sparsh\Brand\Model\BrandProduct                           $brandProduct             brandProduct
     * @param \Sparsh\Brand\Helper\Data                                  $brandhelper              brandhelper
     * @param \Magento\Catalog\Model\Product\Visibility                      $productVisibility        productVisibility
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory                    $urlRewriteFactory        urlRewriteFactory
     * @param \Magento\Framework\Filesystem                                  $filesystem               filesystem
     * @param \Magento\Framework\Image\AdapterFactory                        $imageFactory             imageFactory
     * @param \Magento\Framework\View\Result\PageFactory                     $resultPageFactory        resultPageFactory
     * @param \Magento\Framework\Filesystem\Io\File                          $file                     file
     * @param array                                                          $data                     data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Sparsh\Brand\Model\Brand $brand,
        \Sparsh\Brand\Model\BrandProduct $brandProduct,
        \Sparsh\Brand\Helper\Data $brandhelper,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        File $file,
        array $data = [],
		\Magento\Framework\View\LayoutFactory $layoutFactory = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->listProductBlock = $listProductBlock;
        $this->reviewFactory = $reviewFactory;
        $this->brandProduct = $brandProduct;
        $this->brand = $brand;
        $this->brandhelper = $brandhelper;
        $this->productVisibility = $productVisibility;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->file = $file;
		$this->layoutFactory = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
        parent::__construct($context,$data);
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $brandId = $this->getRequest()->getParam('id');

        $brand = $this->brand->load($brandId);
        $name = $brand->getTitle();

        $breadcumb= $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcumb) {
            $breadcumb->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcumb->addCrumb(
                $name,
                [
                    'label' => $name,
                    'title' => $name,
                ]
            );
        }

        parent::_prepareLayout();

        $valuePerPage =[];
        if ($this->getRequest()->getParam('product_list_mode')=='list') {
            $list = $this->brandhelper->getConfig('catalog/frontend/list_per_page_values');
            $myArray = explode(',', $list);
            foreach ($myArray as $key => $value) {
                $valuePerPage[$value] = $value;
            }
        } else {
            $grid = $this->brandhelper->getConfig('catalog/frontend/grid_per_page_values');
            $myArray = explode(',', $grid);
            foreach ($myArray as $key => $value) {
                $valuePerPage[$value] = $value;
            }
        }

        if ($this->getProductCollection()) {
            $toolbar = $this->getLayout()
                ->createBlock(
                    \Magento\Catalog\Block\Product\ProductList\Toolbar::class,
                    'brand.news.toolbar'
                )->setAvailableLimit($valuePerPage)
                ->setShowPerPage(true)
                ->setShowAmounts(true)
                ->setCollection(
                    $this->getProductCollection()
                );
            $this->setChild('toolbar', $toolbar);

            $this->getProductCollection()->load();

            $pager = $this->getLayout()
                ->createBlock(
                    \Magento\Theme\Block\Html\Pager::class,
                    'brand.news.pager'
                )->setAvailableLimit($valuePerPage)
                ->setShowPerPage(true)
                ->setCollection(
                    $this->getProductCollection()
                );
            $this->setChild('pager', $pager);
            $this->getProductCollection()->load();
        }

        return $this;
    }

    /**
     * Return Toolbar Html
     *
     * @return mixed
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Return pager Html
     *
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * BrandProducts items
     *
     * @return BrandProducts items collection
     */
    public function getBrandProducts()
    {
        return $this->getProductCollection()->getItems();
    }

    /**
     * Product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getProductCollection()
    {
        $page = ($this->getRequest()->getParam('p'))
                ? $this->getRequest()->getParam('p')
                : 1;

        if ($this->getRequest()->getParam('product_list_mode')=='list') {
            $pageSize = ($this->getRequest()->getParam('limit'))
                    ? $this->getRequest()->getParam('limit')
                    : $this->brandhelper->getConfig(
                        'catalog/frontend/list_per_page'
                    );
        } else {
            $pageSize = ($this->getRequest()->getParam('limit'))
                    ? $this->getRequest()->getParam('limit')
                    : $this->brandhelper->getConfig(
                        'catalog/frontend/grid_per_page'
                    );
        }

        $productListOrder = ($this->getRequest()->getParam('product_list_order'))
                    ? $this->getRequest()->getParam('product_list_order')
                    : 'position';

        $productListDir = ($this->getRequest()->getParam('product_list_dir'))
                    ? $this->getRequest()->getParam('product_list_dir')
                    : 'ASC';

        $brandId = $this->getRequest()->getParam('id');

        $productObj = $this->brandProduct->getCollection();
        $productObj->filterBrandProducts($brandId);
        $productIdArray = [];
        foreach ($productObj as $value) {
            $productIdArray[] = $value['product_id'];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility(
            $this->productVisibility->getVisibleInCatalogIds()
        );

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => $productIdArray]);
        $collection->setOrder($productListOrder, $productListDir);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    /**
     * Return Add To Cart Post Params of product
     *
     * @param object $product product
     *
     * @return AddToCartPostParams
     */
    public function getAddToCartPostParameters($product)
    {
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    /**
     * Return Product Rating Summary
     *
     * @param object $product product
     *
     * @return Product Review Ratings
     */
    public function getRatingSummary($product)
    {
        $this->reviewFactory->create()
            ->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        return $ratingSummary;
    }

    /**
     * Return Product Review Count
     *
     * @param object $product product
     *
     * @return Product Review count
     */
    public function getReviewsCount($product)
    {
        $_reviewCount = $product->getRatingSummary()->getReviewsCount();
        return $_reviewCount;
    }

    /**
     * Return Product Price
     *
     * @param \Magento\Catalog\Model\Product $product product
     *
     * @return Product Price with HTML
     */
    public function getPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * Return UrlEncoded CartParamName
     *
     * @return UrlEncodedCartParamName
     */
    public function getCartParamNameURLEncoded()
    {
        return Action::PARAM_NAME_URL_ENCODED;
    }

    /**
     * Get Brands of current Product
     *
     * @return Collection of brands
     */
    public function getBrands()
    {
        $currentStore = $this->storeManager->getStore()->getId();
        $productId = $this->getRequest()->getParam('id');
        $collection = $this->brandProduct->getCollection();
        $collection->filterBrands($productId,$currentStore);
        $brand = $collection->fetchItem();
        return $brand;
    }

    /**
     * Get image url of brand
     *
     * @param string $image image
     *
     * @return string
     */
    public function getBrandImageUrl($image)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).$image;
    }

    /**
     * Get brand url
     *
     * @param int    $brandId  brandId
     * @param string $brandUrl brandUrl
     *
     * @return string
     */
    public function getBrandUrl($brandId, $brandUrl = '')
    {
        $urlRewriteModel = $this->urlRewriteFactory->create();
        $urlRewriteData = $urlRewriteModel->getCollection()
            ->addFieldToFilter('request_path', $brandUrl);
        if (!empty($urlRewriteData->getData())) {
            $url=$this->getUrl($urlRewriteData->getData()[0]['request_path']);
        } else {
            $url=$this->getUrl('brands/index', ['id'=>$brandId]);
        }

        return $url;
    }

    /**
     * Return resized image url
     *
     * @param string   $image  image
     * @param int|null $width  width
     * @param int|null $height height
     *
     * @return string
     */
    public function getResizeUrl($image, $width = null, $height = null)
    {
        $absolutePath = $this->filesystem
            ->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath($image);

        if (!$this->file->fileExists($absolutePath)) {
            return false;
        }

        $resizedImage = str_replace("sparsh/brand/", "", $image);

        $imageResized = $this->filesystem
            ->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('sparsh/brand/resized/'.$width.'/').$resizedImage;

        if (!$this->file->fileExists($imageResized)) {
            $imageResize = $this->imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(true);
            $imageResize->keepAspectRatio(true);
            $imageResize->backgroundColor([255,255,255]);
            $imageResize->resize($width, $height);
            //destination folder
            $destination = $imageResized;
            //save image
            $imageResize->save($destination);
        }

        $resizedURL = $this->storeManager->getStore()
            ->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'sparsh/brand/resized/'.$width.'/'.$resizedImage;
        return $resizedURL;
    }
	
	/**
    * For rendering product swatches layout
    */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout LayoutInterface */
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }
        return $this->rendererListBlock;
    }
}
