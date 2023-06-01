<?php
namespace Dev\RestApi\Model\Api;

use Dev\RestApi\Api\ProductRepositoryInterface;
use Dev\RestApi\Api\ResponseItemListInterfaceFactory;
use Dev\RestApi\Api\ResponseAthleteInterfaceFactory;
use Dev\RestApi\Api\ResponseSliderInterfaceFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\Catalog\Model\ResourceModel\Product\Collection;
use Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Magento\Sales\Model\Order\Payment\State\CaptureCommand;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Amasty\ShopbyBrand\Model\Brand\BrandListDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\CategoryFactory;
use \Magento\Catalog\Model\ProductFactory;
use Mageplaza\BannerSlider\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Amasty\Rma\Observer\RmaEventNames;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use \Psr\Log\LoggerInterface;
use Amasty\Rma\Utils\FileUpload;
use Razorpay\Api\Api;
use \Razorpay\Magento\Model\Config;

/**
 * Class ProductRepository
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ResponseItemListInterfaceFactory
     */
    private $responseItemListFactory;

    /**
     * @var ResponseAthleteInterfaceFactory
     */
    private $responseAthleteInterfaceFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ResponseSliderInterfaceFactory
     */
    private $responseSliderInterfaceFactory;

    /**
     * @var BrandListDataProvider
     */
    protected $brandListDataProvider;

    /**
     * @var Data
     */
    private $sliderHelper;

    /**
     * BrandCollectionFactory
     *
     * @var \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    private $brandCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    private $attributeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected $_logger;

    private $counter;

    const Category = [
        'just_launched' => 217,
        'trending' => 23,
        'deals' => 14
    ];

    const STATUS_PROCESSING = 'processing';

    /**
     * @param Action $productAction
     * @param Collection $productCollection
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param ResponseItemListInterfaceFactory $responseItemListFactory
     */
    public function __construct(
        Action $productAction,
        Collection $productCollection,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        ResponseItemListInterfaceFactory $responseItemListFactory,
        ProductFactory $productFactory,
        Data $sliderHelper,
        ResponseSliderInterfaceFactory $responseSliderInterfaceFactory,
        BrandCollectionFactory $brandCollectionFactory,
        ResponseAthleteInterfaceFactory $responseAthleteInterfaceFactory,
        BrandListDataProvider $brandListDataProvider,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig,
        \Magento\Framework\App\RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->productAction = $productAction;
        $this->productCollection = $productCollection;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->responseItemListFactory = $responseItemListFactory;
        $this->productFactory = $productFactory;
        $this->sliderHelper = $sliderHelper;
        $this->responseSliderInterfaceFactory = $responseSliderInterfaceFactory;
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->responseAthleteInterfaceFactory = $responseAthleteInterfaceFactory;
        $this->brandListDataProvider = $brandListDataProvider;
        $this->catalogConfig = $catalogConfig;
        $this->attributeConfig = $attributeConfig;
        $this->request = $request;
        $this->_logger = $logger;
        $this->counter = 0;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $categoryName
     * @return ResponseItemListInterface
     * @throws NoSuchEntityException
     */
    public function getItemLists(string $categoryName = null, $categoryId = '', $subCategoryId = null, $brandId = '', $searchCriteria = [], $sortBy = '', $orderDirection = 'desc', int $pageSize = 10, int $currentPage = 1)
    {
        $wishlistAttributes = $this->attributeConfig->getAttributeNames('wishlist_item');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $layerResolver = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $data = [];
        $index = 0;

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $layer = $layerResolver->get();

        if ($categoryId || $categoryName || $subCategoryId) {
            if ($categoryId || $categoryName)
                $categoryId = $categoryId ? $categoryId : self::Category[$categoryName];
            $currentCategory = $categoryId;
            $categoryId = $subCategoryId ? explode(",", $subCategoryId) : explode(",", $categoryId);

            foreach ($categoryId as $catId) {
                $category = $this->categoryFactory->create()->load($catId);
                $categoryTitle[] = $category->getData('name');
            }

            $categoryTitle = implode(", ", $categoryTitle);
            $title = in_array(217, $categoryId) ? str_replace('Newly', 'Just', $categoryTitle) : $categoryTitle;

            if ($currentCategory) {
                $layer->setCurrentCategory($currentCategory);
            }
        }

        if ($brandId) {
            $attributeOptionCollection = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');

            $attributeId = $brandId;
            $optionData = $attributeOptionCollection
                ->setPositionOrder('asc')
                ->setIdFilter($attributeId)
                ->setStoreFilter()
                ->load();

            $title = $optionData->getFirstItem()->getValue();
        }

        $products = $layer->getProductCollection()
            ->addFieldToFilter("visibility", [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_SEARCH])
            ->addFieldToFilter("status", Status::STATUS_ENABLED)
            ->addAttributeToSelect('*')
            //   ->addAttributeToSelect($wishlistAttributes)
            ->addFieldToFilter('category_ids', $categoryId)
            ->addFieldToFilter('brand', $brandId)
            ->addUrlRewrite()
            ->setOrder($sortBy, $orderDirection)
            ->setPageSize($pageSize)
            ->setCurPage($currentPage);

        // Filter Products
        if (!empty($searchCriteria[0]['filter']) && !empty($searchCriteria[0]['filter']['attribute']) && !empty($searchCriteria[0]['filter']['value'])) {
            foreach ($searchCriteria as $search) {
                $attributeCode = $search['filter']['attribute'];
                $attributeValue = explode(",", $search['filter']['value']);
                if ($attributeCode === 'price' && count($attributeValue) === 1) {
                    $filterValue = explode("-", $attributeValue[0]);
                    $products->addFieldToFilter('price', ['from' => $filterValue[0], 'to' => $filterValue[1]]);
                } elseif (count($attributeValue) > 1) {
                    $products->addFieldToFilter($attributeCode, $attributeValue);
                } else {
                    $products->addFieldToFilter($attributeCode, $attributeValue[0]);
                }

            }
        }

        $totalCount = $products->getSize();

        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($products as $product) {
            $reviewFactory->getEntitySummary($product, $storeId);

            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            $reviewCount = $product->getRatingSummary()->getReviewsCount();

            $attribute = $product->getResource()->getAttribute('image');
            $imageUrl = $attribute->getFrontend()->getUrl($product);

            $recommBrand = $this->getBrandName($objectManager, $product->getData('entity_id'));

            // get Product data
            $data[$index]['id'] = $product->getData('entity_id');
            $data[$index]['name'] = $product->getData('name');
            $data[$index]['sku'] = $product->getData('sku');
            $data[$index]['type_id'] = $product->getData('type_id');
            $data[$index]['brand_name'] = $product->getAttributeText('brand');
            $data[$index]['image_url'] = $imageUrl;
            $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
            $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
            $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

            if ($customerId && isset($customerId)) {
                $wishlist = 0;

                foreach ($wishlistItems as $item) {
                    if ($product->getData('entity_id') === $item->getData('product_id')) {
                        $wishlist = 1;
                        break;
                    }
                }

                $data[$index]['wishlist'] = $wishlist;
            }

            if ($product->getData('type_id') === 'simple') {
                $data[$index]['price'] = (int) $product->getData('price') ? (int) $product->getData('price') : 0;
                $data[$index]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$index]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $product->getData('price') * 100) . '%' : 0;
            }

            if ($product->getData('type_id') === 'configurable') {
                $data[$index]['price'] = (int) $product->getData('minimal_price') ? (int) $product->getData('minimal_price') : 0;
                $data[$index]['special_price'] = (int) $product->getData('final_price') ? (int) $product->getData('final_price') : 0;
                $data[$index]['discount'] = (int) $product->getData('final_price') ? round(100 - $product->getData('final_price') / $product->getData('minimal_price') * 100) . '%' : 0;
            }

            $index++;
        }

        /** @var ResponseItemListInterface $responseItem */
        $responseItem = $this->responseItemListFactory->create();
        $responseItem->setTitle($title);
        $responseItem->setCategoryId($categoryId ? implode(", ", $categoryId) : '');
        $responseItem->setBrandId($brandId ? $brandId : '');
        $responseItem->setItems($data);
        $responseItem->setTotalCount($totalCount);

        return $responseItem;
    }

    public function getItem($productId)
    {
        $products = $this->productCollection
            ->addFieldToFilter('visibility', ['in' => [4, 2]])
            ->addFieldToFilter('status', ['in' => [1]])
            ->addFieldToFilter('entity_id', ['in' => $productId])
            ->addAttributeToSelect('*')
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addMediaGalleryData()
            ->addUrlRewrite();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $stockItems = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $imageArray = [];
        $imageIndex = 0;
        $index = 0;
        $option = [];
        $optionIndex = 0;

        foreach ($products as $product) {
            $reviewFactory->getEntitySummary($product, $storeId);

            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            $reviewCount = $product->getRatingSummary()->getReviewsCount();

            $attribute = $product->getResource()->getAttribute('image');
            $imageUrl = $attribute->getFrontend()->getUrl($product);

            $recommBrand = $this->getBrandName($objectManager, $product->getData('entity_id'));

            $images = $product->getMediaGalleryImages();

            foreach ($images as $image) {
                $imageArray[$imageIndex]['media_type'] = $image->getData('media_type');
                $imageArray[$imageIndex]['position'] = $image->getData('position');
                $imageArray[$imageIndex]['url'] = $image->getData('url');
                $imageIndex++;
            }

            // get Product data
            $data[$index]['id'] = $product->getData('entity_id');
            $data[$index]['name'] = $product->getData('name');
            $data[$index]['sku'] = $product->getData('sku');
            $data[$index]['short_description'] = $product->getData('short_description');
            $data[$index]['type_id'] = $product->getData('type_id');
            $data[$index]['brand_name'] = $product->getAttributeText('brand');
            $data[$index]['brand_id'] = $product->getData('brand');
            $data[$index]['image_url'] = $imageUrl;
            $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
            $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
            $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

            if ($customerId && isset($customerId)) {
                $wishlist = 0;

                foreach ($wishlistItems as $item) {
                    if ($product->getData('entity_id') === $item->getData('product_id')) {
                        $wishlist = 1;
                        break;
                    }
                }

                $data[$index]['wishlist'] = $wishlist;
            }

            if ($product->getData('type_id') === 'simple') {
                $data[$index]['price'] = (int) $product->getData('price') ? (int) $product->getData('price') : 0;
                $data[$index]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$index]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $product->getData('price') * 100) . '%' : 0;
                $data[$index]['color'] = $product->getAttributeText('color');
                $data[$index]['size'] = $product->getAttributeText('size');
                $qty = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                $data[$index]['qty'] = $qty ? $qty : 0;
            }

            if ($product->getData('type_id') === 'configurable') {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
                $price = $regularPrice->getMinRegularAmount();
                $data[$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                $data[$index]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$index]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $price->getValue() * 100) . '%' : 0;

                $attributes = $product->getTypeInstance()->getConfigurableOptions($product);
                //get the configurable product its childproducts
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);

                foreach ($attributes as $attribute) {
                    foreach ($attribute as $key => $attr) {
                        if (count($attributes) === 1) {
                            foreach ($childProducts as $childProduct) {
                                $stockItem = $stockItems->load($childProduct->getID(), 'product_id');
                                $qty = $StockState->getStockQty($childProduct->getId(), $childProduct->getStore()->getWebsiteId());

                                $childImageArray = [];
                                $childImageIndex = 0;

                                if ($attr['attribute_code'] === 'color') {
                                    $childAttribute = $childProduct->getResource()->getAttribute('image');
                                    $childImageUrl = $childAttribute->getFrontend()->getUrl($childProduct);
                                    $childImages = $childProduct->getMediaGalleryImages();

                                    foreach ($childImages as $childImage) {
                                        $childImageArray[$childImageIndex]['media_type'] = $childImage->getData('media_type');
                                        $childImageArray[$childImageIndex]['position'] = $childImage->getData('position');
                                        $childImageArray[$childImageIndex]['url'] = $childImage->getData('url');
                                        $childImageIndex++;
                                    }
                                }

                                if ($childProduct->getData($attr['attribute_code']) == $attr['value_index'] && $stockItem->getData('is_in_stock')) {
                                    $option[$optionIndex]['name'] = $attr['attribute_code'];
                                    $option[$optionIndex]['name_sku'] = $productAttributeRepository->get($attr['attribute_code'])->getAttributeId();
                                    $option[$optionIndex]['option_title'][$key][$attr['attribute_code']] = $attr['option_title'];
                                    $option[$optionIndex]['option_title'][$key]['id'] = $attr['value_index'];
                                    $option[$optionIndex]['option_title'][$key]['qty'] = $qty ? $qty : 0;
                                    $option[$optionIndex]['option_title'][$key]['price'] = (int) $childProduct->getData('price') ? (int) $childProduct->getData('price') : 0;
                                    $option[$optionIndex]['option_title'][$key]['special_price'] = (int) $childProduct->getData('special_price') ? (int) $childProduct->getData('special_price') : 0;
                                    $option[$optionIndex]['option_title'][$key]['discount'] = (int) $childProduct->getData('special_price') ? round(100 - $childProduct->getData('special_price') / $childProduct->getData('price') * 100) . '%' : 0;
                                    // $option[$optionIndex]['option_title'][$key]['product_id'] = $childProduct->getID();

                                    if ($attr['attribute_code'] === 'color') {
                                        $childAttributeSwatch = $childProduct->getResource()->getAttribute('small_image');
                                        $childSwatchImageUrl = $childAttribute->getFrontend()->getUrl($childProduct);
                                        $option[$optionIndex]['option_title'][$key]['swatch_image_url'] = $childSwatchImageUrl;
                                        $option[$optionIndex]['option_title'][$key]['image_url'] = $childImageUrl;
                                        $option[$optionIndex]['option_title'][$key]['media_galleries'] = $childImageArray;
                                    }
                                }
                            }
                        } else {
                            $option[$optionIndex]['name'] = $attr['attribute_code'];
                            $option[$optionIndex]['name_sku'] = $productAttributeRepository->get($attr['attribute_code'])->getAttributeId();
                            $option[$optionIndex]['option_title'][$key][$attr['attribute_code']] = $attr['option_title'];
                            $option[$optionIndex]['option_title'][$key]['id'] = $attr['value_index'];
                            if ($attr['attribute_code'] === 'color') {
                                foreach ($childProducts as $childProduct) {
                                    // print_r($childProduct->getData('color'));
                                    // print_r(', ');
                                    if ($attr['value_index'] !== $childProduct->getData('color')) {
                                        continue;
                                    }
                                    $childAttributeSwatch = $childProduct->getResource()->getAttribute('small_image');
                                    $childSwatchImageUrl = $childAttributeSwatch->getFrontend()->getUrl($childProduct);
                                    $option[$optionIndex]['option_title'][$key]['swatch_image_url'] = $childSwatchImageUrl;
                                }
                            }
                        }
                    }
                    $option[$optionIndex]['option_title'] = array_values(array_unique($option[$optionIndex]['option_title'], SORT_REGULAR));
                    $optionIndex++;
                }
            }

            $data[$index]['size_guide_url'] = "{$this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)}wysiwyg/smartwave/porto/product/Men_Full_Sleeve_New_Size_Guide.jpg";
            $data[$index]['media_galleries'] = $imageArray;
            $data[$index]['options'] = $option;

            $extradetailsIndex = 0;

            if ($product->getDescription()) {
                $data[$index]['extradetails'][$extradetailsIndex] = ['title' => 'Description', 'details' => strip_tags(html_entity_decode($product->getDescription()))];
                $extradetailsIndex++;
            }
            if ($product->getData('best_use')) {
                $data[$index]['extradetails'][$extradetailsIndex] = ['title' => 'Best use', 'details' => strip_tags(html_entity_decode($product->getData('best_use')))];
                $extradetailsIndex++;
            }
            if ($product->getData('technical_specs')) {
                $data[$index]['extradetails'][$extradetailsIndex] = ['title' => 'Technical specs', 'details' => strip_tags(html_entity_decode($product->getData('technical_specs')))];
                $extradetailsIndex++;
            }
            if ($product->getData('product_care')) {
                $data[$index]['extradetails'][$extradetailsIndex] = ['title' => 'Product care', 'details' => strip_tags(html_entity_decode($product->getData('product_care')))];
                $extradetailsIndex++;
            }
            if ($product->getData('return_tab')) {
                $data[$index]['extradetails'][$extradetailsIndex] = ['title' => 'Return & exchange', 'details' => $product->getData('return_tab')];
                $extradetailsIndex++;
            }

            $index++;
        }

        return $data;
    }

    public function getProductLink($productId, $linkType, int $pageSize = 10, int $currentPage = 1)
    {
        $products = $this->productCollection
            ->addFieldToFilter('visibility', ['in' => [4, 2]])
            ->addFieldToFilter('status', ['in' => [1]])
            ->addFieldToFilter('entity_id', ['in' => $productId]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $index = 0;
        $zeroIndex = 0;
        $totalCount = 0;

        foreach ($products as $product) {
            if ($linkType === 'upsell') {
                $productLink = $product->getUpSellProductCollection();
                $totalCount = count($product->getUpSellProductCollection());
                $title = 'You may also like';
            } elseif ($linkType === 'related') {
                $productLink = $product->getRelatedProductCollection();
                $totalCount = count($product->getUpSellProductCollection());
                $title = 'People also bought';
            } elseif ($linkType === 'crosssell') {
                $productLink = $product->getCrossSellProductCollection();
                $totalCount = count($product->getUpSellProductCollection());
                $title = 'Similar product';
            }

            $productLink->addFieldToFilter('visibility', ['in' => [4, 2]])
                ->addFieldToFilter('status', ['in' => [1]])
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('*')
                ->setPageSize($pageSize)
                ->setCurPage($currentPage);

            if (count($productLink)) {
                $data[$zeroIndex]['title'] = $title;
                foreach ($productLink as $item) {
                    $reviewFactory->getEntitySummary($item, $storeId);

                    $ratingSummary = $item->getRatingSummary()->getRatingSummary();
                    $reviewCount = $item->getRatingSummary()->getReviewsCount();

                    $attribute = $item->getResource()->getAttribute('image');
                    $imageUrl = $attribute->getFrontend()->getUrl($item);

                    $recommBrand = $this->getBrandName($objectManager, $item->getData('entity_id'));

                    // get Product data
                    $data[$zeroIndex]['items'][$index]['id'] = $item->getData('entity_id');
                    $data[$zeroIndex]['items'][$index]['name'] = $item->getData('name');
                    $data[$zeroIndex]['items'][$index]['sku'] = $item->getData('sku');
                    $data[$zeroIndex]['items'][$index]['brand_name'] = $item->getAttributeText('brand');
                    $data[$zeroIndex]['items'][$index]['image_url'] = $imageUrl;
                    $data[$zeroIndex]['items'][$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
                    $data[$zeroIndex]['items'][$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
                    $data[$zeroIndex]['items'][$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

                    if ($item->getData('type_id') === 'simple') {
                        $data[$zeroIndex]['items'][$index]['price'] = (int) $item->getData('price') ? (int) $item->getData('price') : 0;
                        $data[$zeroIndex]['items'][$index]['special_price'] = (int) $item->getData('special_price') ? (int) $item->getData('special_price') : 0;
                        $data[$zeroIndex]['items'][$index]['discount'] = (int) $item->getData('special_price') ? round(100 - $item->getData('special_price') / $item->getData('price') * 100) . '%' : 0;
                    }

                    if ($item->getData('type_id') === 'configurable') {
                        $data[$zeroIndex]['items'][$index]['price'] = (int) $item->getData('minimal_price') ? (int) $item->getData('minimal_price') : 0;
                        $data[$zeroIndex]['items'][$index]['special_price'] = (int) $item->getData('final_price') ? (int) $item->getData('final_price') : 0;
                        $data[$zeroIndex]['items'][$index]['discount'] = (int) $item->getData('final_price') ? round(100 - $item->getData('final_price') / $item->getData('minimal_price') * 100) . '%' : 0;
                    }
                    $index++;
                }
                $data[$zeroIndex]['total_count'] = $totalCount;
            }
        }

        return $data;
    }

    public function getPublicProductReview($productId, $pageSize = 10, $currentPage = 1)
    {
        $id = $productId; // product id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create("Magento\Catalog\Model\Product")->load($id);
        $review = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
        $reviewCollection = $review->create()
            ->addStatusFilter(
                    \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $id
            )
            ->setDateOrder()->addRateVotes();

        $data = [];
        $index = 0;

        $startIndex = ($currentPage - 1) * $pageSize;
        $endIndex = $currentPage * $pageSize;
        $next = false;

        foreach ($reviewCollection as $review) {
            $customerObj = $review->getData('customer_id') ? $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterfaceFactory')->create()->getById($review->getData('customer_id')) : 0;
            $customerGroup = $customerObj && $customerObj->getGroupId() ? $objectManager->create('\Magento\Customer\Api\GroupRepositoryInterface')->getById($customerObj->getGroupId()) : 0;

            if ($customerGroup && $customerGroup->getCode() === 'Athelete')
                continue;

            if ($index >= $endIndex) {
                $next = true;
                break;
            }

            if ($index >= $startIndex && $index < $endIndex) {
                $data[$index]['review_id'] = $review->getData('review_id');
                $data[$index]['nickname'] = $review->getData('nickname');
                $data[$index]['title'] = $review->getData('title');
                $data[$index]['detail'] = $review->getData('detail');
                $data[$index]['created_at'] = $review->getData('created_at');

                $votes = $review->getRatingVotes();
                if (count($votes)) {
                    foreach ($votes as $vote) {
                        $data[$index]['rating'][0][$vote->getData('rating_code')] = (float) ($vote->getPercent() / 20);
                    }
                }
            }

            if ($index > $endIndex)
                break;

            $index++;
        }

        return [["reviews" => array_values($data), 'next' => $next]];
    }

    public function getAtheleteProductReview($productId, $pageSize = 10, $currentPage = 1)
    {
        $id = $productId; // product id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create("Magento\Catalog\Model\Product")->load($id);
        $review = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
        $reviewCollection = $review->create()
            ->addStatusFilter(
                    \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $id
            )
            ->setDateOrder()->addRateVotes();

        $data = [];
        $index = 0;

        $startIndex = ($currentPage - 1) * $pageSize;
        $endIndex = $currentPage * $pageSize;
        $next = false;

        foreach ($reviewCollection as $review) {
            $customerObj = $review->getData('customer_id') ? $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterfaceFactory')->create()->getById($review->getData('customer_id')) : 0;
            $customerGroup = $customerObj && $customerObj->getGroupId() ? $objectManager->create('\Magento\Customer\Api\GroupRepositoryInterface')->getById($customerObj->getGroupId()) : 0;

            if (($customerGroup && $customerGroup->getCode() !== 'Athelete') || !$customerGroup)
                continue;

            if ($index >= $endIndex) {
                $next = true;
                break;
            }

            if ($index >= $startIndex && $index < $endIndex) {
                $data[$index]['review_id'] = $review->getData('review_id');
                $data[$index]['nickname'] = $review->getData('nickname');
                $data[$index]['title'] = $review->getData('title');
                $data[$index]['detail'] = $review->getData('detail');
                $data[$index]['created_at'] = $review->getData('created_at');

                $votes = $review->getRatingVotes();
                if (count($votes)) {
                    foreach ($votes as $vote) {
                        $data[$index]['rating'][0][$vote->getData('rating_code')] = (float) ($vote->getPercent() / 20);
                    }
                }
            }

            if ($index > $endIndex)
                break;

            $index++;
        }

        return [["reviews" => array_values($data), 'next' => $next]];
    }

    public function addProductReview($productId, $customerId = null, $title = '', $detail, $nickname, $rating)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeId = $this->storeManager->getStore()->getId();
        $reviewFactory = $objectManager->create('Magento\Review\Model\ReviewFactory');
        $ratingFactory = $objectManager->get('\Magento\Review\Model\RatingFactory');
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');

        $review = $reviewFactory->create();
        $review->setEntityPkValue($productId)
            ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED) //this will be set to approved 
            ->setTitle($title)
            ->setDetail($detail)
            ->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
            ->setStoreId($storeId)
            ->setStores($storeId)
            ->setCustomerId($customerId) //get dynamically here
            ->setNickname($nickname)
            ->save();

        $ratingCollections = $ratingFactory->create()->getCollection();

        foreach ($rating->toArray() as $ratingCode => $optionId) {
            foreach ($ratingCollections as $ratingCollection) {
                if ($ratingCollection->getRatingCode() === ucfirst($ratingCode)) {
                    $ratingFactory->create()
                        ->setRatingId($ratingCollection->getRatingId())
                        ->setReviewId($review->getId())
                        ->addOptionVote($optionId, $productId);
                }
            }
        }

        $review->aggregate();

        return [["msg" => 'Rating has been saved successfully']];
    }

    public function getAvailableAttribute($productId, $attributeNameTo, $attributeNameFrom, $optionId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $stockItems = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
        $configurable = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        $attributeNameTo = explode(',', $attributeNameTo);
        $attributeNameFrom = explode(',', $attributeNameFrom);
        $optionId = explode(',', $optionId);
        $attributeNameFromCount = count($attributeNameFrom);

        $childItemId = $configurable->getChildrenIds($productId);
        $collection = $productCollectionFactory->create();

        $collection->addAttributeToSelect('*')->addIdFilter($childItemId);
        foreach ($attributeNameTo as $key => $value) {
            $collection->addAttributeToFilter($value, $optionId[$key]);
            $collection->addMediaGalleryData();
        }

        $availableAttribute = [];
        $uniqueAttr = [];
        $images_collection = [];
        

        foreach ($attributeNameFrom as $key => $attrNameFrom) {
            $index = 0;
            // return $collection->getData();
            foreach ($collection as $newkey => $childCollect) {
                $stockItem = $stockItems->load($childCollect->getId(), 'product_id');
                $qty = $StockState->getStockQty($childCollect->getId(), $childCollect->getStore()->getWebsiteId());

                $attribute = $childCollect->getResource()->getAttribute('image');
                $imageUrl = $attribute->getFrontend()->getUrl($childCollect);

                $imageArray = [];
                $imageIndex = 0;

                // $images = $childCollect->getMediaGalleryImages();
                //     // print_r(get_class_methods($images));
                //     print_r($images->toArray());

                if (empty($images_collection)) {
                    $images = $childCollect->getMediaGalleryImages();
                    // print_r(get_class_methods($images));
                    // print_r($images->toArray());
                    foreach ($images as $image) {
                        $imageArray[$imageIndex]['media_type'] = $image->getData('media_type');
                        $imageArray[$imageIndex]['position'] = $image->getData('position');
                        $imageArray[$imageIndex]['url'] = $image->getData('url');
                        $imageIndex++;
                    }
                }

                if ($stockItem->getData('is_in_stock')) {
                    $attrIndex = 0;

                    $availableAttribute[$key]['name'] = $attrNameFrom;
                    $availableAttribute[$key]['name_sku'] = $productAttributeRepository->get($attrNameFrom)->getAttributeId();
                    if (!in_array($childCollect->getAttributeText($attrNameFrom), $uniqueAttr)) {
                    $uniqueAttr[] = $childCollect->getAttributeText($attrNameFrom);
                    $availableAttribute[$key]["option_title"][$index][$attrNameFrom] = $childCollect->getAttributeText($attrNameFrom);
                    $availableAttribute[$key]["option_title"][$index]['id'] = $childCollect->getData($attrNameFrom);

                    if ($attributeNameFromCount == 1) {

                    $availableAttribute[$key]["option_title"][$index]['qty'] = $qty ? $qty : 0;
                    $availableAttribute[$key]["option_title"][$index]['price'] = (int) $childCollect->getData('price') ? (int) $childCollect->getData('price') : 0;
                    $availableAttribute[$key]["option_title"][$index]['special_price'] = (int) $childCollect->getData('special_price') ? (int) $childCollect->getData('special_price') : 0;
                    $availableAttribute[$key]["option_title"][$index]['discount'] = (int) $childCollect->getData('special_price') ? round(100 - $childCollect->getData('special_price') / $childCollect->getData('price') * 100) . '%' : 0;

                    }else{

                    $availableAttribute[$key]["option_title"][$index]['qty'] = 0;
                    $availableAttribute[$key]["option_title"][$index]['price'] = 0;
                    $availableAttribute[$key]["option_title"][$index]['special_price'] = 0;
                    $availableAttribute[$key]["option_title"][$index]['discount'] = 0;

                    }
                    if ($attrNameFrom === 'color') {
                        $availableAttribute[$key]["option_title"][$index]['swatch_image_url'] = $imageUrl;
                        $availableAttribute[$key]["option_title"][$index]['media_galleries'] = $imageArray;
                    }
                    if (in_array('color', $attributeNameTo) && empty($images_collection)) {
                        $images_collection['image_url'] = $imageUrl;
                        $images_collection['media_galleries'] = $imageArray;
                    }

                    $index++;
                }
                
                }
            }//2nd loop
        }//1st loop
        return array(["options" => $availableAttribute, 'images' => $images_collection]);
    }

    public function getBrandName($objectManager, $productId)
    {
        $collection = $objectManager->create('\Sparsh\Brand\Model\Brand')->getCollection();
        $collection->getSelect()->join('sparsh_brand_products as sbp', 'main_table.brand_id = sbp.brand_id', array('main_table.title', 'main_table.url_key'))->where("sbp.product_id = " . $productId);
        $collection->getSelect()->limit(1);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['main_table.title', 'main_table.url_key']);
        $brandData = array();
        foreach ($collection as $brand) {
            $brandData = $brand->getTitle();
        }

        return $brandData;
    }

    public function getSliders()
    {
        $sliders = $this->sliderHelper->getActiveSliders()->addFieldToFilter('name', 'Home Page Slider')->getFirstItem();
        $storeId = (int) $this->storeManager->getStore()->getId();
        $filters = ['for_widget' => true];
        $filters = ['not_empty' => true];

        $items = $this->brandListDataProvider->getList($storeId, $filters, 'name');

        $data = [];
        $index = 0;

        $banners = $this->sliderHelper
            ->getBannerCollection($sliders->getId())
            ->addFieldToFilter('status', 1);

        $totalCount = $banners->getSize();

        foreach ($banners as $banner) {
            $data[$index]['banner_id'] = $banner->getData('banner_id');
            $data[$index]['name'] = $banner->getData('name');
            $data[$index]['status'] = $banner->getData('status');
            $data[$index]['type'] = $banner->getData('type');
            $data[$index]['content'] = $banner->getData('content');
            $data[$index]['image_url'] = $banner->getImageUrl();
            $data[$index]['title'] = $banner->getData('title');
            $data[$index]['newtab'] = $banner->getData('newtab');
            $data[$index]['url_banner'] = $banner->getData('url_banner');
            $data[$index]['brand_id'] = "";
            foreach ($items as $item) {
                if ($item['label'] == $banner->getData('name')) {
                    $data[$index]['brand_id'] = $item['brand_id'];
                }
            }
            $index++;
        }

        /** @var ResponseSliderInterfaceFactory $responseItem */
        $responseItem = $this->responseSliderInterfaceFactory->create();
        $responseItem->setBanners($data);
        $responseItem->setTotalCount($totalCount);

        return $responseItem;
    }

    public function getAthletes(int $pageSize = 10, int $currentPage = 1)
    {
        $brandCollection = $this->brandCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('position', 'ASC')
            ->setOrder('update_time', 'DESC')
            ->setPageSize($pageSize)
            ->setCurPage($currentPage);

        $totalCount = $brandCollection->getSize();


        $title = 'Meet the experts';
        $data = [];
        $index = 0;

        foreach ($brandCollection as $brand) {
            $data[$index]['title'] = $brand->getTitle();
            $data[$index]['description'] = $brand->getDescription();
            $data[$index]['image_url'] = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $brand->getImage();

            $index++;
        }

        /** @var ResponseAthleteInterfaceFactory $responseItem */
        $responseItem = $this->responseAthleteInterfaceFactory->create();
        $responseItem->setTitle($title);
        $responseItem->setItems($data);
        $responseItem->setTotalCount($totalCount);

        return $responseItem;
    }

    public function getBrands(int $pageSize = 10, int $currentPage = 1)
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $filters = ['for_widget' => true];
        $filters = ['not_empty' => true];

        $items = $this->brandListDataProvider->getList($storeId, $filters, 'name');
        $totalCount = count($items);

        $title = 'Biggest brand';
        $data = [];
        $index = 0;

        $startIndex = ($currentPage - 1) * $pageSize;
        $endIndex = $currentPage * $pageSize;
        $endIndex = $totalCount < $endIndex ? $totalCount : $endIndex;

        for ($i = $startIndex; $i < $endIndex; $i++) {
            $data[$index]['id'] = $items[$i]['brand_id'];
            $data[$index]['label'] = $items[$i]['label'];
            $data[$index]['image_url'] = $items[$i]['img'];
            $data[$index]['alt'] = $items[$i]['alt'];
            $data[$index]['short_description'] = $items[$i]['short_description'];
            $index++;
        }

        /** @var ResponseAthleteInterfaceFactory $responseItem */
        $responseItem = $this->responseAthleteInterfaceFactory->create();
        $responseItem->setTitle($title);
        $responseItem->setItems($data);
        $responseItem->setTotalCount($totalCount);

        return $responseItem;
    }

    public function getFilterList($categoryId = '', $brandId = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $filterableAttributes = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

        $layerResolver = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->getInstance()->create(
                \Magento\Catalog\Model\Layer\FilterList::class,
            [
                'filterableAttributes' => $filterableAttributes
            ]
        );

        $layer = $layerResolver->get();
        if ($brandId) {
            $productCollection = $layer->getProductCollection()
                ->addAttributeToFilter('brand', ["in" => $brandId])
                ->addAttributeToFilter("visibility", ["in" => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]])
                ->addAttributeToFilter("status", ["eq" => Status::STATUS_ENABLED]);
        } else {
            $layer->setCurrentCategory($categoryId);

            $productCollection = $layer->getProductCollection()
                ->addCategoriesFilter(['in' => $categoryId])
                ->addAttributeToFilter("visibility", ["in" => [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]])
                ->addAttributeToFilter("status", ["eq" => Status::STATUS_ENABLED]);
        }

        $filters = $filterList->getFilters($layer);
        $filterArray = [];
        $index = 0;

        foreach ($filters as $filter) {
            // $availablefilter = $filter->getRequestVar(); //Gives the request param name such as 'cat' for Category, 'price' for Price
            $availablefilter = (string) $filter->getName(); //Gives Display Name of the filter such as Category,Price etc.

            $items = $filter->getItems(); //Gives all available filter options in that particular filter
            $filterValues = array();
            $j = 0;
            foreach ($items as $item) {
                $f = $item->getFilter();
                $attributeModel = $f->getData('attribute_model');
                $filterValues[$j]['attr_code'] = $filter->getRequestVar();
                $filterValues[$j]['display'] = str_replace("&#039;", "'", strip_tags($item->getLabel()));
                $filterValues[$j]['value'] = $item->getValue();
                // $filterValues[$j]['count']   = $item->getCount(); //Gives no. of products in each filter options
                $j++;
            }
            if (!empty($filterValues) && count($filterValues) > 1) {
                $filterArray[$index]["name"] = $availablefilter;
                $filterArray[$index]["type"] = $attributeModel ? $attributeModel->getFrontendInput() : '';
                $filterArray[$index]["filters"] = $filterValues;
                $index++;
            }
        }
        return $filterArray;
    }

    public function getShopByCategory()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()
            ->addAttributeToSelect('*')
            //   ->addFieldToFilter('level', 2)
            ->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('shop_by_cate', ['eq' => 1]);

        $data = [];
        $index = 0;
        $zeroIndex = 0;
        $data[$zeroIndex]['title'] = 'Shop by category';

        foreach ($categories as $category) {

            $data[$zeroIndex]['categories'][$index]['id'] = $category->getID();
            $data[$zeroIndex]['categories'][$index]['name'] = $category->getName();
            $data[$zeroIndex]['categories'][$index]['image'] = $mediaUrl . str_replace("/pub", "", $category->getImageUrl());

            $index++;
        }

        return $data;
    }

    public function getAds()
    {
        $e_id = $this->request->getParam('e_id');
        $e_id = substr($e_id, 1);
        $e_id = array_map('intval', explode(",", $e_id));
        if ($this->counter > 20) {
            $e_id = array_slice($e_id, 6);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['nin' => $e_id])
            ->addFieldToFilter('brand', ['nin' => ""])
            ->setPageSize(6)
            ->addFieldToFilter('visibility', ['in' => [4, 2]])
            ->addFieldToFilter('status', ['in' => [1]])
            ->getSelect()
            ->orderRand();

        foreach ($collection as $product) {
            $idofbrand[] = $product->getData('brand');
        }
        $idofbrandcount = array_count_values($idofbrand);
        foreach ($idofbrand as $value) {
            if ($idofbrandcount[$value] > 2) {
                $this->counter++;
                return $this->getAds();
            }
        }

        foreach ($collection as $key => $product) {
            $attribute = $product->getResource()->getAttribute('image');
            $imageUrl = $attribute->getFrontend()->getUrl($product);
            $data[$key]['id'] = $product->getId();
            $data[$key]['name'] = $product->getName();
            $data[$key]['type_id'] = $product->getData('type_id');
            $data[$key]['status'] = $product->getData('status');
            $data[$key]['image'] = $imageUrl;
            if ($product->getData('type_id') === 'simple') {
                $data[$key]['price'] = (int) $product->getData('price') ? (int) $product->getData('price') : 0;
                $data[$key]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$key]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $product->getData('price') * 100) . '%' : 0;
            }
            if ($product->getData('type_id') === 'configurable') {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
                $price = $regularPrice->getMinRegularAmount();
                $data[$key]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                $data[$key]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$key]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $price->getValue() * 100) . '%' : 0;
            }
            $data[$key]['brand'] = $product->getAttributeText('brand');
            $data[$key]['brand_id'] = $product->getBrand();
        }
        foreach ($data as $value) {
            $e_id[] = (int) $value['id'];
        }
        if (count($e_id) > 60) {
            $e_id = array_slice($e_id, 6);
        }
        foreach ($collection as $product) {
            $idofproduct[] = $product->getData('entity_id');
        }
        for ($i = 0; $i < count($idofproduct) - 1; $i++) {
            if ($data[$idofproduct[$i]]['brand_id'] == $data[$idofproduct[$i + 1]]['brand_id']) {
                if ($i == 4) {
                    $new = $data[$idofproduct[$i + 1]];
                    $data[$idofproduct[$i + 1]] = $data[$idofproduct[0]];
                    $data[$idofproduct[0]] = $new;
                } else {
                    $new = $data[$idofproduct[$i + 1]];
                    $data[$idofproduct[$i + 1]] = $data[$idofproduct[$i + 2]];
                    $data[$idofproduct[$i + 2]] = $new;
                }
            }
        }
        $newdata = [];
        foreach ($data as $key => $value) {
            array_push($newdata, $value);
        }
        $data = array('product' => $newdata);
        $e_id = array('e_id' => $e_id);
        $finalarray = array();
        array_push($finalarray, $data, $e_id);
        return $finalarray;
    }

    public function getAllCategory(int $pageSize = 10, int $currentPage = 1)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('level', 2)
            ->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('entity_id', ['nin' => self::Category])
            ->setPageSize($pageSize)
            ->setCurPage($currentPage);

        $totalCount = $categories->getSize();
        $data = [];
        $index = 0;
        $zeroIndex = 0;
        $data[$zeroIndex]['title'] = 'Category lists';

        foreach ($categories as $category) {
            $data[$zeroIndex]['categories'][$index]['id'] = $category->getID();
            $data[$zeroIndex]['categories'][$index]['name'] = $category->getName();
            $data[$zeroIndex]['categories'][$index]['image'] = $mediaUrl . str_replace("/pub", "", $category->getData('image'));

            $index++;
        }

        $data[$zeroIndex]['total_count'] = $totalCount;

        return $data;
    }

    public function getBestCategoryDeals()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $data = [
            [
                "title" => 'Best category deals',
                "Categories" => [
                    [
                        'name' => 'SHOES',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpacks_0005_Group.png"
                    ],
                    [
                        'name' => 'BAGPACKS',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpacks_0004_Group.png"
                    ],
                    [
                        'name' => 'BAGPACKS',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpacks_0003_Group.png"
                    ],
                    [
                        'name' => 'HELMET',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpacks_0001_Group.png"
                    ],
                    [
                        'name' => 'GLOVES',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpackscopy.png"
                    ],
                    [
                        'name' => 'GLOVES',
                        'discount_title' => 'UP TO 30% OFF',
                        'image_url' => $mediaUrl . "wysiwyg/smartwave/porto/homepage/Bagpacks_0000_Group.png"
                    ]
                ]
            ]
        ];

        // $jsonResultFactory = $objectManager->create('Magento\Framework\Controller\Result\JsonFactory');
        // $resultJson = $jsonResultFactory->create();
        // $resultJson->setData($data);

        return $data;
    }

    public function getWishlistProducts(int $pageSize = 10, int $currentPage = 1)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $productRepository = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');

        $customerId = $userContext->getUserId();

        $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);

        $items = $wishlist->getItemCollection()
            ->setPageSize($pageSize)
            ->setCurPage($currentPage);

        $totalCount = $items->getSize();

        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $dataIndex = 0;
        $index = 0;

        $data[$dataIndex]['title'] = 'My Wishlist';
        $data[$dataIndex]['items'] = [];

        foreach ($items as $item) {
            $product = $item->getProduct();

            $productData = $productRepository->get($product->getSku());

            $attribute = $product->getResource()->getAttribute('image');
            $imageUrl = $attribute->getFrontend()->getUrl($product);

            $recommBrand = $this->getBrandName($objectManager, $productData->getData('entity_id'));

            // get Product data
            $data[$dataIndex]['items'][$index]['id'] = $product->getData('entity_id');
            $data[$dataIndex]['items'][$index]['name'] = $product->getData('name');
            $data[$dataIndex]['items'][$index]['short_description'] = $product->getData('short_description');
            $data[$dataIndex]['items'][$index]['sku'] = $product->getData('sku');
            $data[$dataIndex]['items'][$index]['image_url'] = $imageUrl;
            $data[$dataIndex]['items'][$index]['brand_name'] = $productData->getAttributeText('brand');
            $data[$dataIndex]['items'][$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

            if ($product->getData('type_id') === 'simple') {
                $data[$dataIndex]['items'][$index]['price'] = (int) $product->getData('price') ? (int) $product->getData('price') : 0;
                $data[$dataIndex]['items'][$index]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$dataIndex]['items'][$index]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $product->getData('price') * 100) . '%' : 0;
            }

            if ($product->getData('type_id') === 'configurable') {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
                $price = $regularPrice->getMinRegularAmount();
                $data[$dataIndex]['items'][$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                $data[$dataIndex]['items'][$index]['special_price'] = (int) $product->getData('special_price') ? (int) $product->getData('special_price') : 0;
                $data[$dataIndex]['items'][$index]['discount'] = (int) $product->getData('special_price') ? round(100 - $product->getData('special_price') / $price->getValue() * 100) . '%' : 0;
            }

            $index++;
        }

        $data[$dataIndex]['total_count'] = $totalCount;

        return $data;
    }

    public function saveProductToWishlist($productId, $qty = 1, $color = 0, $size = 0)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        $product = $productRepository->getById($productId);
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');

        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $customerId = $userContext->getUserId();

        $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);

        // $buyRequest = null; 

        // if ($color && $size) {
        //     $sizeId = $productAttributeRepository->get('size')->getAttributeId();
        //     $colorId = $productAttributeRepository->get('color')->getAttributeId();
        //     $buyRequest = ['qty' => $qty, 'super_attribute' => [$colorId => $color, $sizeId => $size]]; 
        // }elseif ($size && !$color) {
        //     $sizeId = $productAttributeRepository->get('size')->getAttributeId();
        //     $buyRequest = ['qty' => $qty, 'super_attribute' => [$sizeId => $size]]; 
        // }elseif ($color && !$size) {
        //     $colorId = $productAttributeRepository->get('color')->getAttributeId();
        //     $buyRequest = ['qty' => $qty, 'super_attribute' => [$colorId => $color]]; 
        // }elseif ($qty) {
        $buyRequest = ['qty' => $qty];
        // }


        // //add product for wishlist
        $wishlist->addNewItem($product, $buyRequest);

        // //save wishlist
        $wishlist->save();

        return [["msg" => 'Item added to wishlist.']];
    }

    public function removeProductFromWishlist($productId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $customerId = $userContext->getUserId();

        $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);

        $items = $wishlist->getItemCollection();

        foreach ($items as $item) {
            if ($item->getProductId() == $productId) {
                $item->delete();
                $wishlist->save();
                break;
            }
        }

        return [["msg" => 'Item removed from wishlist.']];
    }

    public function createCustomerToken($email)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Customer');
        $tokenModelFactory = $objectManager->create('\Magento\Integration\Model\Oauth\TokenFactory');
        $responseSingleObjectFactory = $objectManager->create('Dev\RestApi\Api\ResponseSingleObjectInterfaceFactory');

        $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
        $customer = $customer->loadByEmail($email);
        $customerToken = $tokenModelFactory->create();
        $token = $customerToken->createCustomerToken($customer->getId())->getToken();

        /** @var ResponseSingleObjectInterface $responseItem */
        $response = $responseSingleObjectFactory->create();
        $response->setToken($token);

        return $response;
    }

    public function createSocialCustomer($email, $firstname, $lastname)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerDataFactory = $objectManager->create('Magento\Customer\Api\Data\CustomerInterfaceFactory');
        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $responseMsgFactory = $objectManager->create('Dev\RestApi\Api\ResponseMsgInterfaceFactory');

        try {
            $customer = $customerDataFactory->create();
            $customer->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($email)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
                ->setCreatedIn($this->storeManager->getStore()->getName());

            $customer = $customerRepository->save($customer);

            /** @var ResponseMsgInterface $responseItem */
            $response = $responseMsgFactory->create();
            $response->setMessage("User Created");

            return $response;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getNewlyProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Smartwave\Filterproducts\Block\Home\NewProductsList');
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $optionIds = $block->getAllOptionIds();
        $sliderItems = array();
        $arr = array();
        $i = 0;
        $maxcount = array();
        $categoryId = null;

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $_categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');

        $categoryTitle = 'Newly Launched'; // Category Name

        $collection = $_categoryFactory->create()->getCollection()->addFieldToFilter('name', ['in' => $categoryTitle]);

        if ($collection->getSize()) {
            $categoryId = $collection->getFirstItem()->getId();
        }

        foreach ($optionIds as $id) {
            $newlyLaunchedcollection = $block->getProducts($id);
            $count = 0;
            $temp = array();
            foreach ($newlyLaunchedcollection as $coll) {
                $temp[] = $coll->getData();
                $count++;
            }
            if (!empty($temp)) {
                $arr[$i] = $temp;
                $maxcount[] = $count;
                $i++;
            }
        }

        if (!empty($arr)) {
            rsort($maxcount);
            for ($i = 0; $i <= $maxcount[0]; $i++) { //4
                $t = 0;
                $temp = array();
                for ($j = 0; $j < count($arr); $j++) { //5
                    if (isset($arr[$j][$i])) {
                        $temp[] = $arr[$j][$i];
                    }
                }
                if (!empty($temp)) {
                    $sliderItems[] = $temp;
                }
            }
        }

        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $index = 0;
        $pageIterator = 0;
        $pageLimit = 15;

        foreach ($sliderItems as $range => $product) {
            foreach ($product as $key => $pro) {
                $pageIterator++;
                $_product = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($pro['entity_id']);
                $reviewFactory->getEntitySummary($_product, $storeId);

                $ratingSummary = $_product->getRatingSummary()->getRatingSummary();
                $reviewCount = $_product->getRatingSummary()->getReviewsCount();

                $attribute = $_product->getResource()->getAttribute('image');
                $imageUrl = $attribute->getFrontend()->getUrl($_product);

                $recommBrand = $this->getBrandName($objectManager, $_product->getData('entity_id'));

                // get Product data
                $data[$index]['id'] = $_product->getData('entity_id');
                $data[$index]['name'] = $_product->getData('name');
                $data[$index]['sku'] = $_product->getData('sku');
                $data[$index]['type_id'] = $_product->getData('type_id');
                $data[$index]['brand_name'] = $_product->getAttributeText('brand');
                $data[$index]['image_url'] = $imageUrl;
                $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
                $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
                $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

                if ($customerId && isset($customerId)) {
                    $wishlist = 0;

                    foreach ($wishlistItems as $item) {
                        if ($_product->getData('entity_id') === $item->getData('product_id')) {
                            $wishlist = 1;
                            break;
                        }
                    }

                    $data[$index]['wishlist'] = $wishlist;
                }

                if ($_product->getData('type_id') === 'simple') {
                    $data[$index]['price'] = (int) $_product->getPrice() ? (int) $_product->getPrice() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $_product->getPrice()) ? round(100 - $_product->getSpecialPrice() / $_product->getPrice() * 100) . '%' : 0;
                }

                if ($_product->getData('type_id') === 'configurable') {
                    $regularPrice = $_product->getPriceInfo()->getPrice('regular_price');
                    $price = $regularPrice->getMinRegularAmount();
                    $data[$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $price->getValue()) ? round(100 - $_product->getSpecialPrice() / $price->getValue() * 100) . '%' : 0;
                }

                $index++;
                if ($pageIterator === $pageLimit) {
                    break;
                }
            }
            if ($pageIterator === $pageLimit) {
                break;
            }
        }

        /** @var ResponseItemListInterface $responseItem */
        $responseItem = $this->responseItemListFactory->create();
        $responseItem->setTitle("Just launched");
        $responseItem->setCategoryId($categoryId);
        $responseItem->setBrandId('');
        $responseItem->setItems($data);
        $responseItem->setTotalCount(15);

        return $responseItem;
    }

    public function getTrendingProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Smartwave\Filterproducts\Block\Home\TrendingProductsList');
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $optionIds = $block->getAllOptionIds();
        $sliderItems = array();
        $arr = array();
        $i = 0;
        $maxcount = array();
        $categoryId = null;

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $_categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');

        $categoryTitle = 'Trending'; // Category Name

        $collection = $_categoryFactory->create()->getCollection()->addFieldToFilter('name', ['in' => $categoryTitle]);

        if ($collection->getSize()) {
            $categoryId = $collection->getFirstItem()->getId();
        }

        foreach ($optionIds as $id) {
            $trendingcollection = $block->getProducts($id);
            $count = 0;
            $temp = array();
            foreach ($trendingcollection as $coll) {
                $temp[] = $coll->getData();
                $count++;
            }
            if (!empty($temp)) {
                $arr[$i] = $temp;
                $maxcount[] = $count;
                $i++;
            }
        }

        if (!empty($arr)) {
            rsort($maxcount);
            for ($i = 0; $i <= $maxcount[0]; $i++) { //4
                $t = 0;
                $temp = array();
                for ($j = 0; $j < count($arr); $j++) { //5
                    if (isset($arr[$j][$i])) {
                        $temp[] = $arr[$j][$i];
                    }
                }
                if (!empty($temp)) {
                    $sliderItems[] = $temp;
                }
            }
        }

        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $index = 0;
        $pageIterator = 0;
        $pageLimit = 15;

        foreach ($sliderItems as $range => $product) {
            foreach ($product as $key => $pro) {
                $pageIterator++;
                $_product = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($pro['entity_id']);
                $reviewFactory->getEntitySummary($_product, $storeId);

                $ratingSummary = $_product->getRatingSummary()->getRatingSummary();
                $reviewCount = $_product->getRatingSummary()->getReviewsCount();

                $attribute = $_product->getResource()->getAttribute('image');
                $imageUrl = $attribute->getFrontend()->getUrl($_product);

                $recommBrand = $this->getBrandName($objectManager, $_product->getData('entity_id'));

                // get Product data
                $data[$index]['id'] = $_product->getData('entity_id');
                $data[$index]['name'] = $_product->getData('name');
                $data[$index]['sku'] = $_product->getData('sku');
                $data[$index]['type_id'] = $_product->getData('type_id');
                $data[$index]['brand_name'] = $_product->getAttributeText('brand');
                $data[$index]['image_url'] = $imageUrl;
                $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
                $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
                $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

                if ($customerId && isset($customerId)) {
                    $wishlist = 0;

                    foreach ($wishlistItems as $item) {
                        if ($_product->getData('entity_id') === $item->getData('product_id')) {
                            $wishlist = 1;
                            break;
                        }
                    }

                    $data[$index]['wishlist'] = $wishlist;
                }

                if ($_product->getData('type_id') === 'simple') {
                    $data[$index]['price'] = (int) $_product->getPrice() ? (int) $_product->getPrice() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $_product->getPrice()) ? round(100 - $_product->getSpecialPrice() / $_product->getPrice() * 100) . '%' : 0;
                }

                if ($_product->getData('type_id') === 'configurable') {
                    $regularPrice = $_product->getPriceInfo()->getPrice('regular_price');
                    $price = $regularPrice->getMinRegularAmount();
                    $data[$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $price->getValue()) ? round(100 - $_product->getSpecialPrice() / $price->getValue() * 100) . '%' : 0;
                }

                $index++;
                if ($pageIterator === $pageLimit) {
                    break;
                }
            }
            if ($pageIterator === $pageLimit) {
                break;
            }
        }

        /** @var ResponseItemListInterface $responseItem */
        $responseItem = $this->responseItemListFactory->create();
        $responseItem->setTitle("Trending");
        $responseItem->setCategoryId($categoryId);
        $responseItem->setBrandId('');
        $responseItem->setItems($data);
        $responseItem->setTotalCount(15);

        return $responseItem;
    }

    public function getDealsProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Smartwave\Filterproducts\Block\Home\SaleList');
        $_productCollection = $block->getLoadedProductCollection();
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $index = 0;
        $pageIterator = 0;
        $pageLimit = 6;

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $optionIds = $block->getAllOptionIds();

        $sliderItems = array();
        $arr = array();
        $i = 0;
        $maxcount = array();

        foreach ($optionIds as $id) {
            $dealcollection = $block->getProducts($id);
            $count = 0;
            $temp = array();
            foreach ($dealcollection as $coll) {
                $temp[] = $coll->getData();
                $count++;
            }
            if (!empty($temp)) {
                $arr[$i] = $temp;
                $maxcount[] = $count;
                $i++;
            }
        }

        if (!empty($arr)) {
            rsort($maxcount);
            for ($i = 0; $i <= $maxcount[0]; $i++) { //4
                $t = 0;
                $temp = array();
                for ($j = 0; $j < count($arr); $j++) { //5
                    if (isset($arr[$j][$i])) {
                        $temp[] = $arr[$j][$i];
                    }
                }
                if (!empty($temp)) {
                    $sliderItems[] = $temp;
                }
            }
        }

        foreach ($sliderItems as $_product): foreach ($_product as $key => $pro):
                $product = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($pro['entity_id']);

                $reviewFactory->getEntitySummary($product, $storeId);

                $ratingSummary = $product->getRatingSummary()->getRatingSummary();
                $reviewCount = $product->getRatingSummary()->getReviewsCount();
                $recommBrand = $this->getBrandName($objectManager, $product->getData('entity_id'));
                $attribute = $product->getResource()->getAttribute('image');
                $imageUrl = $attribute->getFrontend()->getUrl($product);

                $data[$index]['id'] = $pro['entity_id'];
                $data[$index]['name'] = $pro['name'];
                $data[$index]['sku'] = $pro['sku'];
                $data[$index]['type_id'] = $pro['type_id'];
                $data[$index]['brand_name'] = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
                ;
                $data[$index]['image_url'] = $imageUrl;
                $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
                $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
                $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';
                // return $_product;

                if ($pro['type_id'] === 'simple') {
                    $data[$index]['price'] = (int) $pro['price'] ? (int) $pro['price'] : 0;
                    $data[$index]['special_price'] = (int) $pro['special_price'] ? (int) $pro['special_price'] : 0;
                    $data[$index]['discount'] = ((int) $pro['special_price'] && (int) $pro['price']) ? round(100 - $pro['special_price'] / $pro['price'] * 100) . '%' : 0;
                }

                if ($pro['type_id'] === 'configurable') {
                    $data[$index]['price'] = (int) $pro['price'] ? (int) $pro['price'] : 0;
                    $data[$index]['special_price'] = (int) $pro['special_price'] ? (int) $pro['special_price'] : 0;
                    $data[$index]['discount'] = ((int) $pro['special_price'] && (int) $pro['price']) ? round(100 - $pro['special_price'] / $pro['price'] * 100) . '%' : 0;
                }
                $index++;
            endforeach;
        endforeach;

        // foreach ($_productCollection as $_product) {
        //     $pageIterator++;
        //     $reviewFactory->getEntitySummary($_product, $storeId);

        //     $ratingSummary = $_product->getRatingSummary()->getRatingSummary();
        //     $reviewCount = $_product->getRatingSummary()->getReviewsCount();

        //     $attribute = $_product->getResource()->getAttribute('image');
        //     $imageUrl = $attribute->getFrontend()->getUrl($_product);

        //     $recommBrand = $this->getBrandName($objectManager, $_product->getData('entity_id'));

        //     // get Product data
        //     $data[$index]['id'] = $_product->getData('entity_id');
        //     $data[$index]['name'] = $_product->getData('name');
        //     $data[$index]['sku'] = $_product->getData('sku');
        //     $data[$index]['type_id'] = $_product->getData('type_id');
        //     $data[$index]['brand_name'] = $_product->getAttributeText('brand');
        //     $data[$index]['image_url'] = $imageUrl;
        //     $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
        //     $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
        //     $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

        //     if ($customerId && isset($customerId)) {
        //         $wishlist = 0;

        //         foreach ($wishlistItems as $item) {
        //             if ($_product->getData('entity_id') === $item->getData('product_id')) {
        //                 $wishlist = 1;
        //                 break;
        //             }
        //         }

        //         $data[$index]['wishlist'] = $wishlist;
        //     }

        //     if ($_product->getData('type_id') === 'simple') {
        //         $data[$index]['price'] = (int) $_product->getPrice() ? (int) $_product->getPrice() : 0;
        //         $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
        //         $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $_product->getPrice()) ? round(100 - $_product->getSpecialPrice() / $_product->getPrice() * 100) . '%' : 0;
        //     }

        //     if ($_product->getData('type_id') === 'configurable') {
        //         $regularPrice = $_product->getPriceInfo()->getPrice('regular_price');
        //         $price = $regularPrice->getMinRegularAmount();
        //         $data[$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
        //         $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
        //         $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $price->getValue()) ? round(100 - $_product->getSpecialPrice() / $price->getValue() * 100) . '%' : 0;
        //     }

        //     $index++;
        //     if ($pageIterator === $pageLimit) {
        //         break;
        //     }
        // }

        /** @var ResponseItemListInterface $responseItem */
        $responseItem = $this->responseItemListFactory->create();
        $responseItem->setTitle("Deals");
        $responseItem->setCategoryId('');
        $responseItem->setBrandId('');
        $responseItem->setItems($data);
        $responseItem->setTotalCount(6);

        return $responseItem;
    }

    public function getProducts($optionIds, $categoryId, $objectManager)
    {
        $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite()
            ->addAttributeToFilter('brand', array('eq' => $optionIds))
            ->addCategoriesFilter(['in' => $categoryId]);

        return $collection;
    }

    public function getRecentlyViewedProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $this->storeManager->getStore();
        // $recentlyViewed = $objectManager->create('\Magento\Reports\Block\Product\Viewed');
        $quote = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        $customerRepository = $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
        $productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $customerId = $userContext->getUserId();
        // $collection = $recentlyViewed->getItemsCollection();
        // // print_r(get_class_methods($collection));
        // print_r($customerId);
        // print_r('saasd');
        // foreach ($collection as $product) {
        //     echo $product->getName(). '<br />';
        // }

        // $quote = $quote->create(); // Create Quote Object
        // $quote->setStore($store); // Set Store
        // $customer = $customerRepository->getById($customerId);
        // $quote->setCurrency();
        // $quote->assignCustomer($customer);

        // $product = $productFactory->create()->load(104);
        // // $product->setPrice($item['price']);
        // $quote->addProduct($product, intval(1));
        // $quote->save();
    }

    public function buyNow($productId, $productPrice)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $this->storeManager->getStore();
        $quote = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        $customerRepository = $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
        $productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $productAttributeRepository = $objectManager->create('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        $request = $objectManager->create('Magento\Framework\Webapi\Rest\Request');
        $customerId = $userContext->getUserId();

        $quote = $quote->create(); // Create Quote Object
        $quote->setStore($store); // Set Store
        $customer = $customerRepository->getById($customerId);
        $quote->setCurrency();
        $quote->assignCustomer($customer);
        $buyRequest = $request->getBodyParams()['buyRequest'];
        $buyRequest['product'] = $productId;
        $buyRequest = new \Magento\Framework\DataObject($buyRequest);
        $product = $productFactory->create()->load($productId);
        $product->setPrice($productPrice);
        $quote->addProduct($product, $buyRequest);
        $quote->save();
        $quote->collectTotals()->save();

        return $quote->getId();
    }

    public function deleteBuyNow($quoteId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $this->storeManager->getStore();
        $quote = $objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $customerId = $userContext->getUserId();

        $quote = $quote->get($quoteId);
        $quote->setStore($store);
        $quote->setIsActive(0); // Set Store
        $quote->save();

        return [["msg" => "Cart has been removed"]];
    }

    public function createRazorPayOrder($orderId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderModel = $objectManager->get('Magento\Sales\Model\Order')->load($orderId);
        $config = $objectManager->get('\Razorpay\Magento\Model\Config');

        $amount = (int) (number_format($orderModel->getGrandTotal() * 100, 0, ".", ""));

        $receipt_id = $orderModel->getIncrementId();

        $maze_version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        $module_version = $objectManager->get('Magento\Framework\Module\ModuleList')->getOne('Razorpay_Magento')['setup_version'];


        //if already order from same session , let make it's to pending state
        $new_order_status = $config->getNewOrderStatus();

        $orderModel->setState('new')
            ->setStatus($new_order_status)
            ->save();

        try {
            $this->_logger->info("Razorpay Order: create order started with quoteID:" . $receipt_id
                . " and amount:" . $amount);

            $key_id = $config->getConfigData(Config::KEY_PUBLIC_KEY);
            $key_secret = $config->getConfigData(Config::KEY_PRIVATE_KEY);
            $rzp = new Api($key_id, $key_secret);

            $order = $rzp->order->create([
                'amount' => $amount,
                'receipt' => $receipt_id,
                'currency' => $orderModel->getOrderCurrencyCode(),
                // 'payment_capture' => $payment_capture
            ]);

            if (null !== $order && !empty($order->id)) {
                $this->_logger->info("Razorpay Order: order created with rzp_order:" . $order->id);

                $responseContent = [
                    'success' => true,
                    'rzp_order' => $order->id,
                    'order_id' => $receipt_id,
                    'amount' => number_format($order->amount, 2, ".", ""),
                    'quote_currency' => $orderModel->getOrderCurrencyCode(),
                    'quote_amount' => number_format($orderModel->getGrandTotal(), 2, ".", ""),
                    'maze_version' => $maze_version,
                    'module_version' => $module_version,
                ];

                $orderModel->setRzpOrderId($order->id)
                    ->save();
            }
        } catch (\Razorpay\Api\Errors\Error $e) {
            $responseContent = [
                'message' => $e->getMessage(),
                'parameters' => []
            ];
            $this->_logger->critical("Razorpay Order: Error message:" . $e->getMessage());
        } catch (\Exception $e) {
            $responseContent = [
                'message' => $e->getMessage(),
                'parameters' => []
            ];
            $this->_logger->critical("Razorpay Order: Error message:" . $e->getMessage());
        }

        return [$responseContent];
    }

    public function validateRazorPaySignature($razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $receipt_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface');
        $orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');
        $config = $objectManager->get('\Razorpay\Magento\Model\Config');
        $orderSender = $objectManager->get('\Magento\Sales\Model\Order\Email\Sender\OrderSender');


        $orderId = $receipt_id;
        try {
            $collection = $objectManager->get('Magento\Sales\Model\Order')
                ->getCollection()
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('rzp_order_id')
                ->addFilter('increment_id', $orderId)->getFirstItem();

            $razorpayOrderID = $collection->getRzpOrderId();

            $order = $order->load($collection->getEntityId());
        } catch (\Exception $e) {
            $this->_logger->critical("Callback Error: " . $e->getMessage());

            throw new \Exception($e->getMessage());
        }

        try {
            $this->validateSignature($razorpay_payment_id, $razorpay_signature, $razorpayOrderID, $config);

            $orderId = $order->getIncrementId();

            $order->setState(static::STATUS_PROCESSING)
                ->setStatus(static::STATUS_PROCESSING);

            $payment = $order->getPayment();
            $paymentId = $razorpay_payment_id;

            $payment->setLastTransId($paymentId)
                ->setTransactionId($paymentId)
                ->setIsTransactionClosed(true)
                ->setShouldCloseParentTransaction(true);

            $payment->setParentTransactionId($payment->getTransactionId());

            if ($config->getPaymentAction() === \Razorpay\Magento\Model\PaymentMethod::ACTION_AUTHORIZE_CAPTURE) {
                $payment->addTransactionCommentsToOrder("$paymentId", (new CaptureCommand())->execute($payment, $order->getGrandTotal(), $order), "");
            } else {
                $payment->addTransactionCommentsToOrder("$paymentId", (new AuthorizeCommand())->execute($payment, $order->getGrandTotal(), $order), "");
            }

            $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
            $transaction->setIsClosed(true);
            $transaction->save();

            $order->save();

            $orderRepository->save($order);

            //update/disable the quote
            $quote = $objectManager->get('Magento\Quote\Model\Quote')
                ->load($order->getQuoteId());
            $quote->setIsActive(false)
                ->save();

            // if ($order->canInvoice() && ($config->getPaymentAction() === \Razorpay\Magento\Model\PaymentMethod::ACTION_AUTHORIZE_CAPTURE) && 
            //     $config->canAutoGenerateInvoice())
            // {
            //     $invoice = $this
            //         ->_invoiceService
            //         ->prepareInvoice($order);
            //     $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            //     $invoice->setTransactionId($paymentId);
            //     $invoice->register();
            //     $invoice->save();
            //     $transactionSave = $this
            //         ->_transaction
            //         ->addObject($invoice)->addObject($invoice->getOrder());
            //     $transactionSave->save();

            //     $this
            //         ->_invoiceSender
            //         ->send($invoice);
            //     //send notification code
            //     $order->setState(static::STATUS_PROCESSING)
            //         ->setStatus(static::STATUS_PROCESSING);
            //     $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
            //         ->setIsCustomerNotified(true)
            //         ->save();
            // }

            return [['msg' => 'payment is successful']];

        } catch (\Razorpay\Api\Errors\Error $e) {

            $quote = $objectManager->get('Magento\Quote\Model\Quote')
                ->load($order->getQuoteId());

            $quote->setIsActive(1)
                ->setReservedOrderId(null)
                ->save();

            $this->_logger
                ->critical("Validate: Razorpay Error message:" . $e->getMessage());

            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            $quote = $objectManager->get('Magento\Quote\Model\Quote')
                ->load($order->getQuoteId());

            $quote->setIsActive(1)
                ->setReservedOrderId(null)
                ->save();

            $this->_logger
                ->critical("Validate: Exception Error message:" . $e->getMessage());

            throw new \Exception($e->getMessage());
        }
    }

    public function validateSignature($razorpay_payment_id, $razorpay_signature, $razorpayOrderID, $config)
    {
        $attributes = array(
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_order_id' => $razorpayOrderID,
            'razorpay_signature' => $razorpay_signature,
        );

        $key_id = $config->getConfigData(Config::KEY_PUBLIC_KEY);
        $key_secret = $config->getConfigData(Config::KEY_PRIVATE_KEY);
        $rzp = new Api($key_id, $key_secret);
        $rzp->utility->verifyPaymentSignature($attributes);
    }

    public function getReviewByBrand($brandId, $pageSize = 10, $currentPage = 1)
    {
        $products = $this->productCollection
            ->addFieldToFilter('visibility', ['in' => [4, 2]])
            ->addFieldToFilter('status', ['in' => [1]])
            ->addAttributeToSelect('name')
            ->addFieldToFilter('brand', $brandId);

        $data = [];
        $index = 0;

        $startIndex = ($currentPage - 1) * $pageSize;
        $endIndex = $currentPage * $pageSize;
        $next = false;

        foreach ($products as $product) {
            $id = $product->getData('entity_id'); // product id
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create("Magento\Catalog\Model\Product")->load($id);
            $review = $objectManager->get("Magento\Review\Model\ResourceModel\Review\CollectionFactory");
            $reviewCollection = $review->create()
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->addEntityFilter('product', $id)
                ->setDateOrder()
                ->addRateVotes();

            foreach ($reviewCollection as $review) {
                $customerObj = $review->getData('customer_id') ? $objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterfaceFactory')->create()->getById($review->getData('customer_id')) : 0;
                $customerGroup = $customerObj && $customerObj->getGroupId() ? $objectManager->create('\Magento\Customer\Api\GroupRepositoryInterface')->getById($customerObj->getGroupId()) : 0;

                // if ($customerGroup && $customerGroup->getCode() === 'Athelete')
                //     continue;

                if ($index >= $endIndex) {
                    $next = true;
                    break;
                }

                if ($index >= $startIndex && $index < $endIndex) {
                    $data[$index]['review_id'] = $review->getData('review_id');
                    $data[$index]['product_name'] = $product->getData('name');
                    $data[$index]['product_id'] = $product->getData('entity_id');
                    $data[$index]['user_type'] = $customerGroup && !empty($customerGroup->getCode()) ? $customerGroup->getCode() : 'anonymous';
                    $data[$index]['nickname'] = $review->getData('nickname');
                    $data[$index]['title'] = $review->getData('title');
                    $data[$index]['detail'] = $review->getData('detail');
                    $data[$index]['created_at'] = $review->getData('created_at');

                    $data[$index]['rating'] = 0;
                    $votes = $review->getRatingVotes();
                    if (count($votes)) {
                        foreach ($votes as $vote) {
                            $data[$index]['rating'] += (float) ($vote->getPercent() / 60);
                        }
                    }
                }

                if ($index > $endIndex)
                    break;

                $index++;
            }
        }

        return [["reviews" => array_values($data), "next" => $next]];
    }

    public function getReturnOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Amasty\Rma\Block\Returns\NewReturn');

        $reasons = $block->getReasons();
        $conditions = $block->getConditions();
        $resolutions = $block->getResolutions();

        $list = [];
        $reasonIndex = 0;
        $conditionIndex = 0;
        $resolutionIndex = 0;

        foreach ($reasons as $reason) {
            $list['reasons'][$reasonIndex]['reason_id'] = $reason->getData('reason_id');
            $list['reasons'][$reasonIndex]['label'] = $reason->getData('label');
            $list['reasons'][$reasonIndex]['payer'] = $reason->getData('payer');
            $list['reasons'][$reasonIndex]['position'] = $reason->getData('position');

            $reasonIndex++;
        }

        foreach ($conditions as $condition) {
            $list['conditions'][$conditionIndex]['condition_id'] = $condition->getData('condition_id');
            $list['conditions'][$conditionIndex]['label'] = $condition->getData('label');
            $list['conditions'][$conditionIndex]['position'] = $condition->getData('position');

            $conditionIndex++;
        }

        foreach ($resolutions as $resolution) {
            $list['resolutions'][$resolutionIndex]['resolution_id'] = $resolution->getData('resolution_id');
            $list['resolutions'][$resolutionIndex]['label'] = $resolution->getData('label');
            $list['resolutions'][$resolutionIndex]['position'] = $resolution->getData('position');

            $resolutionIndex++;
        }

        return [$list];
    }

    public function createReturnOrderItem()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->create('Magento\Framework\Webapi\Rest\Request');
        $orderRepository = $objectManager->get('Magento\Sales\Api\OrderRepositoryInterface');
        $requestRepository = $objectManager->get('Amasty\Rma\Api\CustomerRequestRepositoryInterface');
        $frontendRma = $objectManager->create('Amasty\Rma\Controller\FrontendRma');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $configProvider = $objectManager->get('Amasty\Rma\Model\ConfigProvider');

        $customerId = $userContext->getUserId();

        $files = [];
        $files = $request->getFiles()->toArray();
        if (!empty($files)) {
            $files = $this->uploadFiles($files, $configProvider, $objectManager);
        }

        $orderId = (int) $request->getParam('order');

        try {
            $order = $orderRepository->get($orderId);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Order not found.'));
        }

        $items = $request->getParam('items');

        if ($configProvider->isReturnPolicyEnabled() && !$request->getParam('rmapolicy')) {
            throw new CouldNotSaveException(__('You didn\'t agree to Privacy policy'));
        }

        $req = $this->create(
            $this->processNewRequest(
                $requestRepository,
                $order,
                $request,
                $customerId,
                $objectManager
            ),
            $objectManager
        );

        if (!empty($comment = $request->getParam('comment')) || !(empty($files))) {
            $frontendRma->saveNewReturnMessage($req, $comment, $files);
        }

        return [['message' => 'Added item to return']];
    }

    public function processNewRequest($requestRepository, $order, $httpRequest, $customerId, $objectManager)
    {
        $customFieldFactory = $objectManager->create('\Amasty\Rma\Api\Data\RequestCustomFieldInterfaceFactory');
        $customFields = [];
        $request = $requestRepository->getEmptyRequestModel();

        if (!empty($httpRequest->getParam('custom_fields'))) {
            foreach ($httpRequest->getParam('custom_fields') as $code => $label) {
                $customFields[] = $customFieldFactory->create([
                    'key' => $code,
                    'value' => $label
                ]);
            }
        }

        $request->setStoreId($this->storeManager->getStore()->getId())
            ->setOrderId($order->getEntityId())
            ->setCustomerName(
                $order->getBillingAddress()->getFirstname() . ' '
                . $order->getBillingAddress()->getLastname()
            )->setCustomFields($customFields);

        $request->setCustomerId($customerId);

        $returnItems = [];

        foreach ($httpRequest->getParam('items') as $itemId => $item) {
            if (
                empty($item['return']) || empty($item['qty']) || $item['qty'] < 0.0001
                || empty($item['condition']) || empty($item['reason']) || empty($item['resolution'])
            ) {
                continue;
            }

            $returnItems[] = $requestRepository->getEmptyRequestItemModel()
                ->setQty((float) $item['qty'])
                ->setResolutionId((int) $item['resolution'])
                ->setReasonId((int) $item['reason'])
                ->setConditionId((int) $item['condition'])
                ->setOrderItemId((int) $itemId);
        }

        $request->setRequestItems($returnItems);

        return $request;
    }

    public function create(\Amasty\Rma\Api\Data\RequestInterface $request, $objectManager)
    {
        $createReturnProcessor = $objectManager->get('\Amasty\Rma\Api\CreateReturnProcessorInterface');
        $eventManager = $objectManager->get('\Magento\Framework\Event\ManagerInterface');
        $requestRepository = $objectManager->get('\Amasty\Rma\Api\RequestRepositoryInterface');

        if (!($returnOrder = $createReturnProcessor->process($request->getOrderId()))) {
            throw new CouldNotSaveException(__('Wrong Order.'));
        }

        $requestItems = $request->getRequestItems();
        $returnOrderItems = $returnOrder->getItems();
        $resultItems = [];

        foreach ($requestItems as $requestItem) {
            $item = false;
            foreach ($returnOrderItems as $returnOrderItem) {
                if ($returnOrderItem->getItem()->getItemId() == $requestItem->getOrderItemId()) {
                    $item = $returnOrderItem;
                    break;
                }
            }

            if (
                $item && $item->isReturnable() && $requestItem->getQty() <= $item->getAvailableQty()
                && isset($item->getResolutions()[$requestItem->getResolutionId()])
            ) {
                $requestItem->setRequestQty($requestItem->getQty());
                $resultItems[] = $requestItem;
            }
        }

        if (empty($resultItems)) {
            throw new CouldNotSaveException(__('Items were not selected'));
        }

        $request->setRequestItems($resultItems);

        $eventManager->dispatch(RmaEventNames::BEFORE_CREATE_RMA_BY_CUSTOMER, ['request' => $request]);
        $requestRepository->save($request);
        $eventManager->dispatch(RmaEventNames::RMA_CREATED_BY_CUSTOMER, ['request' => $request]);

        return $request;
    }

    public function uploadFiles($files, $configProvider, $objectManager)
    {
        $files = $files['attach-files'];

        if (!$files) {
            return null;
        }

        $maxFileSize = (int) $configProvider->getMaxFileSize();
        list($result, $errors) = $this->uploadFile($files, $maxFileSize);

        if ($errors) {
            $result['error'] = __(
                'Files %1 have exceeded the maximum file size limit of %2 KB.',
                implode(',', $errors),
                $maxFileSize
            );
        }

        return $result;
    }

    public function uploadFile($files, $maxFileSize)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $uploader = $objectManager->get('Magento\Framework\Api\Uploader');
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $mathRandom = $objectManager->get('Magento\Framework\Math\Random');

        $path = $filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
                FileUpload::MEDIA_PATH . 'temp/'
            );

        $writer = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $writer->create($path);

        $result = [];
        $errors = [];

        foreach ($files as $name => $file) {
            if ($maxFileSize > 0 && ($file['size'] > $maxFileSize * 1024)) {
                $errors[] = $file['name'];
                continue;
            }

            //phpcs:ignore
            $extension = mb_strtolower('.' . pathinfo($file['name'], PATHINFO_EXTENSION));

            $fileHash = $mathRandom->getUniqueHash() . $extension;

            if ($writer->isExist($path . $fileHash)) {
                $this->deleteTemp($fileHash);
            }

            try {
                $fileAttributes = [
                    'tmp_name' => $file['tmp_name'],
                    'name' => $file['name']
                ];
                $uploader->processFileAttributes($fileAttributes);
                $uploader->setAllowedExtensions(FileUpload::ALLOWED_EXTENSIONS);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($path, $fileHash);

                $result[] = [
                    FileUpload::FILEHASH => $fileHash,
                    FileUpload::FILENAME => (string) $file['name'],
                    FileUpload::EXTENSION => $extension
                ];
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->_logger->critical($e);
                }
            }
        }

        return [$result, $errors];
    }

    public function cancelReturnOrderItem($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerRequestRepository = $objectManager->get('Amasty\Rma\Api\CustomerRequestRepositoryInterface');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $customerId = $userContext->getUserId();
        $response = $customerRequestRepository->closeRequest($id, $customerId);

        return [["msg" => "Return Item has been cancel"]];
    }

    public function getCouponList()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteIdMaskFactory = $objectManager->create('Magento\Quote\Model\QuoteIdMaskFactory');
        $quoteRepository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $quoteFactory = $objectManager->get('\Magento\Quote\Model\QuoteFactory');
        $couponList = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $customer = $objectManager->create('\Magento\Customer\Model\Customer');

        $customerId = $userContext->getUserId();
        $customer = $customer->load($customerId);

        $quote = $quoteFactory->create()->loadByCustomer($customerId);

        $coupon_list = $this->getValidCouponList($quote, $customer->getGroupId());
        print_r($coupon_list);
    }

    public function getValidCouponList($quote, $customerGroupId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->create('\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
        $utility = $objectManager->create('\Magento\SalesRule\Model\Utility');

        $address = $quote->getShippingAddress();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $customerGroupId;
        $rules = $collectionFactory->create()
            ->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
            ->addAllowedSalesRulesFilter()
            ->addFieldToFilter('coupon_type', ['neq' => '1']);
        // ->addFieldToFilter('is_visible_in_list', ['eq' => '1']);
        $ruleArray = [];

        $items = $quote->getAllVisibleItems();

        foreach ($rules as $rule) {
            $validate = $utility->canProcessRule($rule, $address);
            print_r($validate);
            // print_r($items);

            $validAction = false;

            foreach ($items as $item) {
                if ($validAction = $rule->getActions()->validate($item)) {
                    break;
                }
            }

            print_r($rule->getData());

            if ($validate && $validAction) {
                $ruleArray[] = [
                    'name' => $rule->getName(),
                    'description' => $rule->getDescription(),
                    'coupon' => $rule->getCode(),
                ];
            }
        }

        return $ruleArray;
    }

    public function getInvoiceList()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollectionFactory = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $invoiceCollectionFactory = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $customerId = $userContext->getUserId();

        $orders = $orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_id', $customerId);

        $orderids = $orders->getColumnValues('entity_id');

        if (count($orderids)) {
            $invoices = $invoiceCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', ['in' => $orderids])
                ->setOrder('created_at', 'desc');
        }

        $invoiceDetails = [];
        $index = 0;

        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoiceDetails[$index]['invoice_id'] = $invoice->getIncrementId();
                // $invoiceDetails[$index]['invoice_id']  = $invoice->getId();
                $invoiceDetails[$index]['order_id'] = $invoice->getOrderId();
                $details = $this->getInvoiceDetails($invoice->getOrderId(), $objectManager);
                $invoiceDetails[$index]['purchase_date'] = isset($details['purchaseDate']) ? $details['purchaseDate'] : "";
                $index++;
            }
        }

        return $invoiceDetails;
    }

    public function getInvoiceDetails($orderId, $objectManager)
    {
        $orderRepository = $objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
        $addressCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory');
        $timezone = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        $order = $orderRepository->get($orderId);

        /* check order is not virtual */
        if (!$order->getIsVirtual()) {
            $orderShippingId = $order->getShippingAddressId();
            return [
                "purchaseDate" => $timezone->date($order->getCreatedAt())->format('F d, Y')
            ];
        }

        return [];
    }

    public function sendOtp($mobile_no)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Session');
        $customerHelper = $objectManager->create('Mega\Phonelogin\Helper\CustomerHelper');
        $resourceConfig = $objectManager->create('\Magento\Config\Model\ResourceModel\Config');
        $eventManager = $objectManager->get('\Magento\Framework\Event\ManagerInterface');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $helper = $objectManager->create('Mega\Phonelogin\Helper\Data');

        $customerId = $userContext->getUserId();

        $customer->setData('mobile_verified', 0);

        $this->_logger->info("SendOTP");
        $this->_logger->info("1Mobile No - " . $mobile_no);
        // $this->_logger->info("1OTP -" . $otp);

        $respArray = [];
        $mobilePhone = $mobile_no;
        if (isset($mobilePhone)) {
            $isVerified = $customerHelper->checkVerifiedNumber($customerId, $mobilePhone);
            if ($isVerified) {
                $customer->setData('mobile_verified', 1);
                $respArray = array('status' => false, 'message' => __('Mobile Number Has Already Been Verified'));
                return [$respArray];
            }

            $status = $helper->sendVerificationCode($mobilePhone);
            $eventManager->dispatch('mega_phonelogin_sendverificationcode_after', ['event_data' => $status]);

            // $this->_logger->info("1Mobile No - after ");
            // $this->_logger->info("1Mobile No - " . print_r($customer->getData(), true));

            if ($status['status']) {
                $creditCounts = $helper->getConfiguration('mega_phonelogin/api/available_credits');
                $remainingCount = $creditCounts - 1;
                if ($remainingCount > 0)
                    $resourceConfig->saveConfig(
                        'mega_phonelogin/api/available_credits',
                        $creditCounts - 1,
                        'default',
                        0
                    );
                $respArray['status'] = true;
                $respArray['message'] = 'A Verification code has been sent on your mobile number';
            } else {
                $respArray['status'] = false;
                $respArray['message'] = __('An Error occurred sending the verification code');
            }
            $this->_logger->info("1Mobile No - after ");
            $this->_logger->info("1Mobile No - " . print_r($customer->getData(), true));
            return [$respArray];
        }
        $respArray['status'] = false;
        $respArray['message'] = __('Error occurred sending the verification code');
        return [$respArray];
    }

    public function verifyNumber($mobile_no)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Session');
        $customerHelper = $objectManager->create('Mega\Phonelogin\Helper\CustomerHelper');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');

        $customerId = $userContext->getUserId();

        $customer->setData('mobile_verified', 0);

        $respArray = [];
        $mobilePhone = $mobile_no;
        if (isset($mobilePhone)) {
            $isVerified = $customerHelper->checkVerifiedNumber($customerId, $mobilePhone);
            if ($isVerified) {
                $customer->setData('mobile_verified', 1);
                $respArray = array('status' => true, 'message' => __('Mobile Number Has Already Been Verified'));
                return [$respArray];
            }

            $respArray['status'] = false;
            $respArray['message'] = __('Please verify your mobile number.');

            return [$respArray];
        }
        $respArray['status'] = false;
        $respArray['message'] = __('Error occurred while verifying mobile number');
        return [$respArray];
    }

    public function verifyOtp($mobile_no, $otp)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_session = $objectManager->create('\Magento\Customer\Model\Session');
        // $customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        // $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $phone = $mobile_no;
        $code = $otp;
        $_session->setData('mobile_verified', 0);
        $this->_logger->info("VerifyOTP");
        $this->_logger->info("2Mobile No - " . $mobile_no);
        $this->_logger->info("2OTP - " . $otp);

        // $customerId = $userContext->getUserId();
// echo $phone;
        $isValid = $_session->getData($mobile_no);
        $this->_logger->info("isValid - " . $isValid);
        $this->_logger->info("isValid - " . print_r($_session->getData(), true));
        // var_dump($_session->getData());
        if ($isValid) {
            if ($code == $isValid) {
                // echo "if";
                $_session->setData('mobile_verified', 1);
                // $customer = $customerRepositoryInterface->getById($customerId);
                // $customer->setCustomAttribute('mphone_number', $phone);
                // $customerRepositoryInterface->save($customer);
                $_session->setData('mobile_verified_oncheckout', 1);
                $resp = array('status' => true, 'message' => __('Mobile Number Has Been Verified'));
                $this->_logger->info("isValid - " . print_r($resp, true));
                return [$resp];
            } else {
                // echo "else";
                $_session->setData('mobile_verified', 0);
                $_session->setData('mobile_verified_oncheckout', 0);
                $resp = array('status' => false, 'message' => __('Invalid Verification Code 1'));
                $this->_logger->info("isValid - " . print_r($resp, true));
                return [$resp];
            }
        }
        // echo "outside";
        $_session->setData('mobile_verified', 0);
        $_session->setData('mobile_verified_oncheckout', 0);
        $resp = array('status' => false, 'message' => __('Invalid Verification Code 2'));
        $this->_logger->info("isValid - " . print_r($resp, true));
        return [$resp];
    }

    public function getAllAthletes()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Customer');
        $groupObj = $objectManager->create('\Magento\Customer\Model\Group');

        $cusGrpCode = 'Athelete';

        $existingGroup = $groupObj->load($cusGrpCode, 'customer_group_code');

        $customers = $customer->getCollection()
            ->addAttributeToSelect("*")
            ->addFieldToFilter("group_id", $existingGroup->getData('customer_group_id'))
            ->load();

        $data = [];
        $index = 0;

        foreach ($customers as $cust) {
            $data[$index]['name'] = $cust->getData('firstname') . " " . $cust->getData('lastname');
            $data[$index]['email'] = $cust->getData('email');
            $index++;
        }

        return $data;
    }

    public function homeComponents()
    {

        /** Banner Section **/

        $sliders = $this->sliderHelper->getActiveSliders()->addFieldToFilter('name', 'Home Page Slider')->getFirstItem();
        $storeId = (int) $this->storeManager->getStore()->getId();
        $filters = ['for_widget' => true];
        $filters = ['not_empty' => true];

        $items = $this->brandListDataProvider->getList($storeId, $filters, 'name');

        $bannerData = [];
        $bannerIndex = 0;

        $banners = $this->sliderHelper
            ->getBannerCollection($sliders->getId())
            ->addFieldToFilter('status', 1);

        $totalCount = $banners->getSize();

        foreach ($banners as $banner) {
            $bannerData[$bannerIndex]['banner_id'] = $banner->getData('banner_id');
            $bannerData[$bannerIndex]['name'] = $banner->getData('name');
            $bannerData[$bannerIndex]['status'] = $banner->getData('status');
            $bannerData[$bannerIndex]['type'] = $banner->getData('type');
            $bannerData[$bannerIndex]['content'] = $banner->getData('content');
            $bannerData[$bannerIndex]['image_url'] = $banner->getImageUrl();
            $bannerData[$bannerIndex]['title'] = $banner->getData('title');
            $bannerData[$bannerIndex]['newtab'] = $banner->getData('newtab');
            $bannerData[$bannerIndex]['url_banner'] = $banner->getData('url_banner');
            $bannerData[$bannerIndex]['brand_id'] = "";
            foreach ($items as $item) {
                if ($item['label'] == $banner->getData('name')) {
                    $bannerData[$bannerIndex]['brand_id'] = $item['brand_id'];
                }
            }
            $bannerIndex++;
        }

        /** Newly/Just Launched Section **/

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Smartwave\Filterproducts\Block\Home\NewProductsList');

        $categoryTitle = 'Newly Launched'; // Category Name

        $pageLimit = 10;

        $newlyLaunchedSection = $this->shuffleProduct($objectManager, $block, $categoryTitle, $pageLimit, $title = "Just launched");

        /** Shop By Category Section **/

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()
            ->addAttributeToSelect('*')
            //   ->addFieldToFilter('level', 2)
            ->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('shop_by_cate', ['eq' => 1]);

        $shopByCategoryData = [];
        $shopByCategoryIndex = 0;

        foreach ($categories as $category) {

            $shopByCategoryData[$shopByCategoryIndex]['id'] = $category->getID();
            $shopByCategoryData[$shopByCategoryIndex]['name'] = $category->getName();
            $shopByCategoryData[$shopByCategoryIndex]['image'] = $mediaUrl . str_replace("/pub", "", $category->getImageUrl());

            $shopByCategoryIndex++;
        }

        $response = [
            [
                "banner_section" => [
                    "banners" => $bannerData,
                    "total_count" => $totalCount
                ],
                "justlaunched_section" => $newlyLaunchedSection,
                "shopbycategory_section" => [
                    "title" => 'Shop by category',
                    "categories" => $shopByCategoryData
                ]
            ]
        ];

        return $response;
    }

    public function homeComponentSection2()
    {
        /* Trending Section */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->get('\Smartwave\Filterproducts\Block\Home\TrendingProductsList');

        $categoryTitle = 'Trending'; // Category Name

        $pageLimit = 10;

        $trendingSection = $this->shuffleProduct($objectManager, $block, $categoryTitle, $pageLimit);

        /* Brand Section */

        $storeId = (int) $this->storeManager->getStore()->getId();
        $filters = ['for_widget' => true];
        $filters = ['not_empty' => true];

        $items = $this->brandListDataProvider->getList($storeId, $filters, 'name');
        $totalCount = count($items);

        $brandtitle = 'Biggest brand';
        $brandData = [];
        $brandIndex = 0;

        $startIndex = 0;
        $endIndex = 10;
        $endIndex = $totalCount < $endIndex ? $totalCount : $endIndex;

        for ($i = $startIndex; $i < $endIndex; $i++) {
            $brandData[$brandIndex]['id'] = $items[$i]['brand_id'];
            $brandData[$brandIndex]['label'] = $items[$i]['label'];
            $brandData[$brandIndex]['image_url'] = $items[$i]['img'];
            $brandData[$brandIndex]['alt'] = $items[$i]['alt'];
            $brandData[$brandIndex]['short_description'] = $items[$i]['short_description'];
            $brandIndex++;
        }

        /* Deals Section */

        $dealBlock = $objectManager->get('\Smartwave\Filterproducts\Block\Home\SaleList');
        $dealCategoryTitle = 'Deals'; // Category Name

        $pageLimit = 10;

        $dealSection = $this->shuffleProduct($objectManager, $dealBlock, $dealCategoryTitle, $pageLimit);

        $response = [
            [
                "trending_section" => $trendingSection,
                "brand_section" => [
                    "title" => $brandtitle,
                    "items" => $brandData,
                    "total_count" => $totalCount
                ],
                "deal_section" => $dealSection,
            ]
        ];

        return $response;
    }

    public function shuffleProduct($objectManager, $block, $categoryTitle, $pageLimit, $title = "")
    {
        $wishlistFactory = $objectManager->create('\Magento\Wishlist\Model\WishlistFactory');
        $userContext = $objectManager->create('\Magento\Authorization\Model\CompositeUserContext');
        $optionIds = $block->getAllOptionIds();
        $sliderItems = array();
        $arr = array();
        $i = 0;
        $maxcount = array();
        $categoryId = null;

        $customerId = $userContext->getUserId();

        if ($customerId && isset($customerId)) {
            $wishlist = $wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlistItems = $wishlist->getItemCollection();
        }

        $_categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');

        $collection = $_categoryFactory->create()->getCollection()->addFieldToFilter('name', ['in' => $categoryTitle]);

        if ($collection->getSize()) {
            $categoryId = $collection->getFirstItem()->getId();
        }

        foreach ($optionIds as $id) {
            $productCollection = $block->getProducts($id);
            $count = 0;
            $temp = array();
            foreach ($productCollection as $coll) {
                $temp[] = $coll->getData();
                $count++;
            }
            if (!empty($temp)) {
                $arr[$i] = $temp;
                $maxcount[] = $count;
                $i++;
            }
        }

        if (!empty($arr)) {
            rsort($maxcount);
            for ($i = 0; $i <= $maxcount[0]; $i++) { //4
                $t = 0;
                $temp = array();
                for ($j = 0; $j < count($arr); $j++) { //5
                    if (isset($arr[$j][$i])) {
                        $temp[] = $arr[$j][$i];
                    }
                }
                if (!empty($temp)) {
                    $sliderItems[] = $temp;
                }
            }
        }

        $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $storeId = $this->storeManager->getStore()->getId();
        $data = [];
        $index = 0;
        $pageIterator = 0;

        foreach ($sliderItems as $range => $product) {
            foreach ($product as $key => $pro) {
                $pageIterator++;
                $_product = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($pro['entity_id']);
                $reviewFactory->getEntitySummary($_product, $storeId);

                $ratingSummary = $_product->getRatingSummary()->getRatingSummary();
                $reviewCount = $_product->getRatingSummary()->getReviewsCount();

                $attribute = $_product->getResource()->getAttribute('image');
                $imageUrl = $attribute->getFrontend()->getUrl($_product);

                $recommBrand = $this->getBrandName($objectManager, $_product->getData('entity_id'));

                // get Product data
                $data[$index]['id'] = $_product->getData('entity_id');
                $data[$index]['name'] = $_product->getData('name');
                $data[$index]['sku'] = $_product->getData('sku');
                $data[$index]['type_id'] = $_product->getData('type_id');
                $data[$index]['brand_name'] = $_product->getAttributeText('brand');
                $data[$index]['image_url'] = $imageUrl;
                $data[$index]['rating'] = $ratingSummary ? (float) ($ratingSummary / 20) : 0;
                $data[$index]['no_of_reviews'] = $reviewCount ? $reviewCount : 0;
                $data[$index]['recommend_by'] = $recommBrand ? $recommBrand : '';

                if ($customerId && isset($customerId)) {
                    $wishlist = 0;

                    foreach ($wishlistItems as $item) {
                        if ($_product->getData('entity_id') === $item->getData('product_id')) {
                            $wishlist = 1;
                            break;
                        }
                    }

                    $data[$index]['wishlist'] = $wishlist;
                }

                if ($_product->getData('type_id') === 'simple') {
                    $data[$index]['price'] = (int) $_product->getPrice() ? (int) $_product->getPrice() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $_product->getPrice()) ? round(100 - $_product->getSpecialPrice() / $_product->getPrice() * 100) . '%' : 0;
                }

                if ($_product->getData('type_id') === 'configurable') {
                    $regularPrice = $_product->getPriceInfo()->getPrice('regular_price');
                    $price = $regularPrice->getMinRegularAmount();
                    $data[$index]['price'] = (int) $price->getValue() ? (int) $price->getValue() : 0;
                    $data[$index]['special_price'] = (int) $_product->getSpecialPrice() ? (int) $_product->getSpecialPrice() : 0;
                    $data[$index]['discount'] = ((int) $_product->getSpecialPrice() && (int) $price->getValue()) ? round(100 - $_product->getSpecialPrice() / $price->getValue() * 100) . '%' : 0;
                }

                $index++;
                if ($pageIterator === $pageLimit) {
                    break;
                }
            }
            if ($pageIterator === $pageLimit) {
                break;
            }
        }

        $result = [
            "title" => $title ? $title : $categoryTitle,
            "category_id" => $categoryId,
            "items" => $data,
            "total_count" => $pageLimit
        ];

        return $result;
    }

}