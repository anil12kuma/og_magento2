<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class SystemConfig
{
    const XML_PATH_IS_ENABLE_MODULE = 'ms_shopbybrand/general/enable_frontend';

    const XML_PATH_IS_SHOW_MENU_BAR = 'ms_shopbybrand/general/brand_link_menu';
    
    const XML_PATH_IS_DISPLAY_TOP_LINK = 'ms_shopbybrand/general/display_toplink';

    const XML_PATH_NUM_OF_BRANDS_MENU_BAR = 'ms_shopbybrand/general/number_of_brands_display';

    const XML_PATH_DISPLAY_FEATURED_BRAND = 'ms_shopbybrand/brand_list/display_featured_brand';

    const XML_PATH_DISPLAY_BRAND_CATEGORY = 'ms_shopbybrand/brand_list/display_brand_category';

    const XML_PATH_DISPLAY_BRAND_CHARACTER_LIST = 'ms_shopbybrand/brand_list/display_brand_character_list';

    const XML_PATH_DISPLAY_BRAND_SEARCH_BOX = 'ms_shopbybrand/brand_list/display_brand_search_box';

    const XML_PATH_DISPLAY_BRAND_IMAGE = 'ms_shopbybrand/brand_list/display_brand_image';

    const XML_PATH_DISPLAY_BRAND_GROUP_BY_NAME = 'ms_shopbybrand/brand_list/display_brand_group_by_name';

    const XML_PATH_BRAND_LOGO_WIDTH = 'ms_shopbybrand/brand_list/brand_logo_width';

    const XML_PATH_BRAND_LOGO_HEIGHT = 'ms_shopbybrand/brand_list/brand_logo_height';

    const XML_PATH_DISPLAY_PRODUCT_NUMBER = 'ms_shopbybrand/brand_list/display_product_number';

    const XML_PATH_DISPLAY_BRAND_HAVE_PRODUCT = 'ms_shopbybrand/brand_list/display_brand_have_product';

    const XML_PATH_SHOW_FEATURED_BRAND_AS_SLIDER = 'ms_shopbybrand/brand_list/show_featured_brand_as_slider';

    const XML_PATH_SHOW_IS_DISPLAY_SIDEBAR = 'ms_shopbybrand/brand_listing_sidebar/listing_sb_enable';

    const XML_PATH_NUM_OF_BRANDS_SIDEBAR = 'ms_shopbybrand/brand_listing_sidebar/listing_sb_num_brand';

    const XML_PATH_OPTION_DISPLAY_SIDEBAR = 'ms_shopbybrand/brand_listing_sidebar/listing_sb_display_option';

    const XML_PATH_FRONTEND_URL_PATH = 'ms_shopbybrand/general/frontend_url_path';

    const XML_PATH_NUMBER_OF_PRODUCT_SHOW = 'ms_shopbybrand/brand_details/number_of_products_show';

    const XML_PATH_IS_FEATURE_PRODUCT = 'ms_shopbybrand/brand_details/details_display_featured_products';

    const XML_PATH_BRAND_DETAIL_PAGE_LAYOUT = 'ms_shopbybrand/brand_details/details_layout';

    const XML_PATH_BRAND_DETAIL_DISPLAY_BANNER = 'ms_shopbybrand/brand_details/details_display_banner';

    const XML_PATH_BRAND_DETAIL_DISPLAY_LOGO = 'ms_shopbybrand/brand_details/details_display_logo';

    const XML_PATH_BRAND_DETAIL_DISPLAY_BY_CATEGORY = 'ms_shopbybrand/brand_details/details_display_brand_by_category';

    const XML_PATH_ADVANCE_CONFIG_ADVANCE_DISPLAY = 'ms_shopbybrand/advance_config/advance_display';

    const XML_PATH_ADVANCE_CONFIG_ADVANCE_SHOW_SLIDER = 'ms_shopbybrand/advance_config/advance_show_slider';

    const XML_PATH_STYLE_BACKGROUND_TITLE = 'ms_shopbybrand/style_config/background_title';

    const XML_PATH_STYLE_BRAND_TITLE_COLOR = 'ms_shopbybrand/style_config/brand_title_color';

    const XML_PATH_STYLE_BACKGROUND_FILTER_LABEL = 'ms_shopbybrand/style_config/background_filter_label';

    const XML_PATH_STYLE_COLOR_FILTER_LABEL = 'ms_shopbybrand/style_config/color_filter_label';

    const XML_PATH_STYLE_BACKGROUND_FILTER_LINK = 'ms_shopbybrand/style_config/background_filter_link';

    const XML_PATH_STYLE_BACKGROUND_FILTER_LINK_HOVER = 'ms_shopbybrand/style_config/background_filter_link_hover';

    const XML_PATH_STYLE_COLOR_LINK_FILTER = 'ms_shopbybrand/style_config/color_link_fitler';

    const XML_PATH_STYLE_COLOR_LINK_FILTER_HOVER = 'ms_shopbybrand/style_config/color_filter_link_hover';

    const XML_PATH_STYLE_COLOR_LINK = 'ms_shopbybrand/style_config/color_link';

    const XML_PATH_STYLE_COLOR_LINK_HOVER = 'ms_shopbybrand/style_config/color_link_hover';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * SystemConfig constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $path
     * @param string $scopeType
     * @param null $store
     *
     * @return mixed
     */
    public function getConfig($path, $scopeType = ScopeInterface::SCOPE_STORE, $store = null)
    {
        if ($store === null) {
            $store = $this->_storeManager->getStore()->getId();
        }

        return $this->_scopeConfig->getValue(
            $path,
            $scopeType,
            $store
        );
    }

    /**
     * Check enable frontend.
     *
     * @return mixed
     */

    public function isEnable()
    {
        return $this->getConfig(self::XML_PATH_IS_ENABLE_MODULE);
    }

    /**
     * @return mixed
     */
    public function showMenuBar()
    {
        return $this->getConfig(self::XML_PATH_IS_SHOW_MENU_BAR);
    }

    /**
     * @return mixed
     */
    public function isDisplayTopLink()
    {
        return $this->getConfig(self::XML_PATH_IS_DISPLAY_TOP_LINK);
    }

    /**
     * @return mixed
     */
    public function numberOfBrandsMenuBar()
    {
        return $this->getConfig(self::XML_PATH_NUM_OF_BRANDS_MENU_BAR);
    }

    /**
     * @return mixed
     */
    public function getFrontendUrlPath()
    {
        return $this->getConfig(self::XML_PATH_FRONTEND_URL_PATH);
    }

    /**
     * @return mixed
     */
    public function isDisplaySidebar()
    {
        return $this->getConfig(self::XML_PATH_SHOW_IS_DISPLAY_SIDEBAR);
    }

    /**
     * @return mixed
     */
    public function isDisplayFeatureProduct()
    {
        return $this->getConfig(self::XML_PATH_IS_FEATURE_PRODUCT);
    }

    /**
     * @return mixed
     */
    public function getNumOfBrands()
    {
        return $this->getConfig(self::XML_PATH_NUM_OF_BRANDS_SIDEBAR);
    }

    /**
     * @return mixed
     */
    public function optionDisplay()
    {
        return $this->getConfig(self::XML_PATH_OPTION_DISPLAY_SIDEBAR);
    }

    /**
     * @return mixed
     */
    public function isDisplayFeaturedBrand()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_FEATURED_BRAND);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandCategory()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_CATEGORY);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandCharacterList()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_CHARACTER_LIST);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandSearchBox()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_SEARCH_BOX);
    }


    /**
     * @return mixed
     */
    public function isDisplayOtherPlaces()
    {
        return $this->getConfig(self::XML_PATH_ADVANCE_CONFIG_ADVANCE_DISPLAY);
    }

    /**
     * @return mixed
     */
    public function isDisplayOtherPlacesShowAsSlider()
    {
        return $this->getConfig(self::XML_PATH_ADVANCE_CONFIG_ADVANCE_SHOW_SLIDER);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandImage()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_IMAGE);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandGroupByName()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_GROUP_BY_NAME);
    }

    /**
     * @return mixed
     */
    public function logoWidth()
    {
        return $this->getConfig(self::XML_PATH_BRAND_LOGO_WIDTH);
    }

    /**
     * @return mixed
     */
    public function logoHeight()
    {
        return $this->getConfig(self::XML_PATH_BRAND_LOGO_HEIGHT);
    }

    /**
     * @return mixed
     */
    public function isDisplayProductNumber()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_PRODUCT_NUMBER);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandHaveProduct()
    {
        return $this->getConfig(self::XML_PATH_DISPLAY_BRAND_HAVE_PRODUCT);
    }

    /**
     * @return mixed
     */
    public function showFeatureBrandAsSlider()
    {
        return $this->getConfig(self:: XML_PATH_SHOW_FEATURED_BRAND_AS_SLIDER);
    }

    /**
     * @return mixed
     */
    public function numberOfProductShow()
    {
        return $this->getConfig(self:: XML_PATH_NUMBER_OF_PRODUCT_SHOW);
    }

    /**
     * @return mixed
     */
    public function getBrandDetailPageLayout()
    {
        return $this->getConfig(self::XML_PATH_BRAND_DETAIL_PAGE_LAYOUT);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandDetailBanner()
    {
        return $this->getConfig(self::XML_PATH_BRAND_DETAIL_DISPLAY_BANNER);
    }

    /**
     * @return mixed
     */
    public function isDisplayBrandLogo()
    {
        return $this->getConfig(self::XML_PATH_BRAND_DETAIL_DISPLAY_LOGO);
    }

    /**
     * @return mixed
     */
    public function isDisPlayBrandDetailsByCategory()
    {
        return $this->getConfig(self::XML_PATH_BRAND_DETAIL_DISPLAY_BY_CATEGORY);
    }

    /**
     * @return mixed
     */
    public function getStyleBackgroundTitle()
    {
        return $this->getConfig(self::XML_PATH_STYLE_BACKGROUND_TITLE);
    }

    /**
     * @return mixed
     */
    public function getStyleBrandTitleColor()
    {
        return $this->getConfig(self::XML_PATH_STYLE_BRAND_TITLE_COLOR);
    }

    /**
     * @return mixed
     */
    public function getStyleBackgroundFilterLabel()
    {
        return $this->getConfig(self::XML_PATH_STYLE_BACKGROUND_FILTER_LABEL);
    }

    /**
     * @return mixed
     */
    public function getStyleColorFilterLabel()
    {
        return $this->getConfig(self::XML_PATH_STYLE_COLOR_FILTER_LABEL);
    }

    /**
     * @return mixed
     */
    public function getStyleBackgroundFilterLink()
    {
        return $this->getConfig(self::XML_PATH_STYLE_BACKGROUND_FILTER_LINK);
    }

    /**
     * @return mixed
     */
    public function getStyleBackgroundFilterLinkHover()
    {
        return $this->getConfig(self::XML_PATH_STYLE_BACKGROUND_FILTER_LINK_HOVER);
    }

    /**
     * @return mixed
     */
    public function getStyleColorLinkFilter()
    {
        return $this->getConfig(self::XML_PATH_STYLE_COLOR_LINK_FILTER);
    }

    /**
     * @return mixed
     */
    public function getStyleColorFilterLinkHover()
    {
        return $this->getConfig(self::XML_PATH_STYLE_COLOR_LINK_FILTER_HOVER);
    }

    /**
     * @return mixed
     */
    public function getStyleColorLink()
    {
        return $this->getConfig(self::XML_PATH_STYLE_COLOR_LINK);
    }

    /**
     * @return mixed
     */
    public function getStyleColorLinkHover()
    {
        return $this->getConfig(self::XML_PATH_STYLE_COLOR_LINK_HOVER);
    }
}
