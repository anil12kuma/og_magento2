<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddToTopMenu implements ObserverInterface
{
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $catalogCategory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatState;

    /**
     * @var MenuCategoryData
     */
    protected $menuCategoryData;

    /**
     * @var \Magestore\Shopbybrand\Model\SystemConfig
     */
    protected $_systemConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Shopbybrand\Model\Brand
     */
    protected $_brandModel;

    /**
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData
     */
    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData,
        \Magestore\Shopbybrand\Model\SystemConfig $systemConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Shopbybrand\Model\Brand $brandModel
    ) {
        $this->catalogCategory = $catalogCategory;
        $this->categoryFlatState = $categoryFlatState;
        $this->menuCategoryData = $menuCategoryData;
        $this->_systemConfig = $systemConfig;
        $this->_storeManager = $storeManager;
        $this->_brandModel = $brandModel;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /*configuration info*/
        $isEnableModule = $this->_systemConfig->isEnable();
        $urlPath = $this->_systemConfig->getFrontendUrlPath();
        $isShowMenuBar = $this->_systemConfig->showMenuBar();
        $numberOfBrandsMenuBar = $this->_systemConfig->numberOfBrandsMenuBar();
        /*add to top menu*/
        $block = $observer->getEvent()->getBlock();
        $block->addIdentity(\Magento\Catalog\Model\Category::CACHE_TAG);
        $this->_addCategoriesToMenu($this->catalogCategory->getStoreCategories(), $observer->getMenu(), $block);
        if ($isEnableModule && $isShowMenuBar) {
            //
            $brandData = [
                'name'       => 'Brands',
                'id'         => 'brand',
                'url'        => $this->_storeManager->getStore()->getUrl($urlPath),
                'has_active' => false,
                'is_active'  => false,
            ];

            $parentCategoryNode = $observer->getMenu();
            $tree = $parentCategoryNode->getTree();
            $categoryNode = new \Magento\Framework\Data\Tree\Node($brandData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);
            /*add child*/
            $brandCollection = $this->_brandModel->getBrandsData();
            $tree = $categoryNode->getTree();
            $i = 0;

            $childNode = [
                'name'       => __('View All Brands'),
                'id'         => 'view_all_brand',
                'url'        => $this->_storeManager->getStore()->getUrl($urlPath),
                'has_active' => false,
                'is_active'  => false,
            ];
            $subNode = new \Magento\Framework\Data\Tree\Node($childNode, 'id', $tree, $categoryNode);
            $categoryNode->addChild($subNode);

            foreach ($brandCollection as $brand) {
                if ($i < $numberOfBrandsMenuBar || !$numberOfBrandsMenuBar) {
                    $childNode = [
                        'name'       => $brand['brand_name'],
                        'id'         => 'brand_node_' . $brand['brand_id'],
                        'url'        => $this->_storeManager->getStore()->getUrl($brand['url_key']),
                        'has_active' => false,
                        'is_active'  => false,
                    ];
                    $subNode = new \Magento\Framework\Data\Tree\Node($childNode, 'id', $tree, $categoryNode);
                    $categoryNode->addChild($subNode);
                    $i++;
                }
            }
        }
    }

    /**
     * Recursively adds categories to top menu
     *
     * @param \Magento\Framework\Data\Tree\Node\Collection|array $categories
     * @param \Magento\Framework\Data\Tree\Node $parentCategoryNode
     * @param \Magento\Theme\Block\Html\Topmenu $block
     *
     * @return void
     */
    protected function _addCategoriesToMenu($categories, $parentCategoryNode, $block)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $block->addIdentity(\Magento\Catalog\Model\Category::CACHE_TAG . '_' . $category->getId());

            $tree = $parentCategoryNode->getTree();
            $categoryData = $this->menuCategoryData->getMenuCategoryData($category);
            $categoryNode = new \Magento\Framework\Data\Tree\Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if ($this->categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $block);
        }
    }
}
