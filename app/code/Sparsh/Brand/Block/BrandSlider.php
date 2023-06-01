<?php
/**
 * Class BrandSlider
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

use Sparsh\Brand\Model\Brand;
use Magento\Framework\View\Element\Template;
use Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Filesystem\Io\File;
use Sparsh\Brand\Model\ResourceModel\BrandProduct;

/**
 * Class BrandSlider
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class BrandSlider extends Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Sparsh\Brand\Model\ResourceModel\Brand
     */
    protected $resourceBrand;

    /**
     * BrandCollectionFactory
     *
     * @var \Sparsh\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $brandCollectionFactory;

    /**
     * BrandProductCollectionFactory
     *
     * @var \Sparsh\Brand\Model\ResourceModel\BrandProduct\CollectionFactory
     */
    protected $brandProductCollectionFactory;

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
     * UrlRewriteFactory
     *
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * DirectoryList
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * FileSystem
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * CustomerSession
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Context
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * BrandSlider constructor.
     * @param Context $context
     * @param \Sparsh\Brand\Model\ResourceModel\Brand $resourceBrand
     * @param CollectionFactory $brandCollectionFactory
     * @param BrandProduct\CollectionFactory $brandProductCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param File $file
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Sparsh\Brand\Model\ResourceModel\Brand $resourceBrand,
        CollectionFactory $brandCollectionFactory,
        BrandProduct\CollectionFactory $brandProductCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        File $file,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storeManager = $context->getStoreManager();
        $this->resourceBrand= $resourceBrand;
        $this->brandCollectionFactory = $brandCollectionFactory;
        $this->brandProductCollectionFactory = $brandProductCollectionFactory;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->_dir = $dir;
        $this->file = $file;
        $this->_customerSession = $customerSession;
        $this->_httpContext = $httpContext;
    }

    /**
     * Initialize Block BrandSlider
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("Sparsh_Brand::brand_slider.phtml");
    }

    /**
     * Return Brand Collection
     *
     * @return mixed
     */
    public function getBrandCollection()
    {
        $brandCollection = $this->brandCollectionFactory->create()
            ->addFieldToFilter('status', Brand::STATUS_ENABLED)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('position', 'ASC')
            ->setOrder('update_time', 'DESC');

        $brands =[];

        if ($brandCollection->getSize()>0) {
            foreach ($brandCollection as $brand) {

                $customerGroupIds = $this->resourceBrand->lookupCustomerGroupIds((int)$brand->getId());

                if($this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)){
                    $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
                    if(in_array($customerGroupId, $customerGroupIds))
                    {
                        $brands[$brand->getId()] = [
                            'title'=>$this->escapeHtml($brand->getTitle()),
                            'imageSize'=>$this->getBrandImagePath($brand),
                            'brandUrl'=>$this->escapeUrl($this->getBrandUrl($brand->getId(), $brand->getUrlKey())),
                            'brandImageUrl'=>$this->escapeUrl($this->getBrandImageUrl($brand)),
                            'resizeUrl'=>$this->escapeUrl($this->getResizeUrl($brand->getImage(), 210, 50))
                        ];
                    }
                }
                else
                {
                    if(in_array(0, $customerGroupIds))
                    {
                        $brands[$brand->getId()] = [
                            'title'=>$this->escapeHtml($brand->getTitle()),
                            'imageSize'=>$this->getBrandImagePath($brand),
                            'brandUrl'=>$this->escapeUrl($this->getBrandUrl($brand->getId(), $brand->getUrlKey())),
                            'brandImageUrl'=>$this->escapeUrl($this->getBrandImageUrl($brand)),
                            'resizeUrl'=>$this->escapeUrl($this->getResizeUrl($brand->getImage(), 210, 50))
                        ];
                    }
                }
            }

        }

        return $brands;
    }

    /**
     * Return Brand Image Path
     *
     * @param Brand $brand brand
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandImagePath(Brand $brand)
    {
        return $this->_dir->getPath('media')."/".$brand->getImage();
    }

    /**
     * Return Brand Image Full Url
     *
     * @param Brand $brand brand
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandImageUrl(Brand $brand)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).$brand->getImage();
    }

    /**
     * Return Brand Url By id
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
     * Return resized brand image url
     *
     * @param string   $image  Imagepath
     * @param int|null $width  resizeImageWidth
     * @param int|null $height resizeImageHeight
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
            $destination = $imageResized ;
            //save image
            $imageResize->save($destination);
        }

        $resizedURL = $this->storeManager->getStore()
            ->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'sparsh/brand/resized/'.$width.'/'.$resizedImage;
        return $resizedURL;
    }
}
