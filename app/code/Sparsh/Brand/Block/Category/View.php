<?php
/**
 * Class View
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Block\Category;

/**
 * Class View
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */

class View extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * CategoryHelper
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * BrandModel
     *
     * @var \Sparsh\Brand\Model\Brand
     */
    protected $brand;

    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Current Brand
     *
     * @var object
     */
    protected $currentBrand;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context        context
     * @param \Magento\Catalog\Model\Layer\Resolver            $layerResolver  layerResolver
     * @param \Magento\Framework\Registry                      $registry       registry
     * @param \Magento\Catalog\Helper\Category                 $categoryHelper categoryHelper
     * @param \Sparsh\Brand\Model\Brand                    $brand          brand
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager   storeManager
     * @param array                                            $data           data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Sparsh\Brand\Model\Brand $brand,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        array $data = []
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->coreRegistry = $registry;
        $this->brand = $brand;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->response = $response;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock(\Magento\Catalog\Block\Breadcrumbs::class);

        $brandId = $this->getRequest()->getParam('id');

        $brand = $this->brand->load($brandId);

        $this->currentBrand = $brand;

        if ($brand) {

            if($brand->getStatus() != '1')
            {
                $this->redirect->redirect($this->response, '/');
            }

            $title = $brand->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }

            $metaDescription = $brand->getMetaDescription();
            if ($metaDescription) {
                $this->pageConfig->setDescription($metaDescription);
            }

            $keywords = $brand->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }

            if ($this->categoryHelper->canUseCanonicalTag()) {
                $this->pageConfig->addRemotePageAsset(
                    $brand->getUrlKey(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentBrand()->getTitle());
            }
        }

        return $this;
    }

    /**
     * Return Brand Image Url
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
     * Get Current brand
     *
     * @return \Sparsh\Brand\Model\Brand
     */
    public function getCurrentBrand()
    {
        return $this->currentBrand;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return method_exists($this->getCurrentCategory(), 'getIdentities') && !empty($this->getCurrentCategory())
            ? $this->getCurrentCategory()->getIdentities()
            : [];
    }
}
