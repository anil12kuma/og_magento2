<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block;

/**
 * @category Magestore
 * @package  Magestore_Shopbybrand
 * @module   Shopbybrand
 * @author   Magestore Developer
 */
class Featurebrand extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    protected $_template = 'Magestore_Shopbybrand::featuredbrand.phtml';

    /**
     * @var \Magestore\Shopbybrand\Helper\Data
     */
    protected $_helper;

    /**
     * Featurebrand constructor.
     *
     * @param Context $context
     * @param \Magestore\Shopbybrand\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getFeaturedBrands()
    {
        $config = $this->getSystemConfig()->getConfig('ms_shopbybrand/advance_config/advance_display');
        $handles = $this->getLayout()->getUpdate()->getHandles();
        if(in_array('brand_index_index', $handles)){
            return $this->_helper->getFeaturedBrands();
        }else{
            if($config == 0){
                return [];
            }else{
                return $this->_helper->getFeaturedBrands();
            }
        }
    }

    function showFeatureBrandAsSlider()
    {
        $config = $this->getSystemConfig()->getConfig('ms_shopbybrand/advance_config/advance_display');
        $handles = $this->getLayout()->getUpdate()->getHandles();
        if(in_array('brand_index_index', $handles)){
            return $this->getSystemConfig()->showFeatureBrandAsSlider();
        }else{
            if($config == 0){
                return false;
            }else{
                return $this->getSystemConfig()->isDisplayOtherPlacesShowAsSlider();
            }
        }
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function getBrandUrl($brand)
    {
        return $this->getUrl($brand->getUrlKey(), []);
    }
}
