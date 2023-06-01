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
class Brand extends \Magestore\Shopbybrand\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Shopbybrand::shopbybrand.phtml';

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    protected $_brandFactory;

    /**
     * Brand constructor.
     *
     * @param Context $context
     * @param \Magestore\Shopbybrand\Model\BrandFactory $brandFactory
     * @param array $data
     */
    public function __construct(
        \Magestore\Shopbybrand\Block\Context $context,
        \Magestore\Shopbybrand\Model\BrandFactory $brandFactory,
        array $data = []
    ) {
        $this->_brandFactory = $brandFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magestore\Shopbybrand\Model\BrandFactory
     */
    public function brandFactory()
    {
        return $this->_brandFactory;
    }

    /**
     * @return mixed
     */
    public function generateListCharacter()
    {
        $begin = $this->getRequest()->getParam("begin");

        return $begin;
    }

    /**
     * @return array
     */
    public function getListCharacterBegin()
    {
        $lists = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'W',
            'U',
            'V',
            'X',
            'Y',
            'Z',
        ];

        return $lists;
    }

    /**
     * @param $begin
     *
     * @return string
     */
    public function getCharSearchUrl($begin)
    {
        $setlink = '';
        $lists = $this->getListCharacterBegin();
        if (!in_array($begin, $lists)) {
            return $url = $this->getUrl($setlink, []);
        }

        return $this->getUrl($setlink . "/index/index/begin/" . $begin) . '#shopbybrand_char_filter';
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function getBrandUrl($brand)
    {
        $url = $this->getUrl($brand->getUrlKey(), []);

        return $url;
    }

    /**
     * @return mixed
     */
    public function getInputSearch()
    {
        return $this->getRequest()->getParam('input');
    }

    /**
     * @param $str
     */
    public function getCateClass($str)
    {
        if(!empty($str)){
            $array = explode(',', $str);
            foreach ($array as $key) {
                if ($key != '') {
                    echo ' c' . $key;
                }
            }
        }
    }


    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBrandCategoryHtml()
    {
        return $this->getLayout()
            ->createBlock('Magestore\Shopbybrand\Block\Brandcategories')
            ->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeatureBrandHtml()
    {
        return $this->getLayout()
            ->createBlock('Magestore\Shopbybrand\Block\Featurebrand')
            ->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSearchBoxHtml()
    {
        return $this->getLayout()
            ->createBlock('Magestore\Shopbybrand\Block\Searchbox')
            ->toHtml();
    }
}
