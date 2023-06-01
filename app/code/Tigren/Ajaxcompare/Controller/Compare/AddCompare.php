<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2022 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\Ajaxcompare\Controller\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Tigren\Ajaxcompare\Helper\Data as AjaxcompareData;

/**
 * Class AddCompare
 *
 * @package Tigren\Ajaxcompare\Controller\Compare
 */
class AddCompare extends Action
{
    /**
     * @var AjaxcompareData Data
     */
    protected $_ajaxCompareHelper;

    /**
     * @var Data
     */
    protected $_jsonEncode;

    /**
     * @var null
     */
    protected $_coreRegistry = null;
    protected $productRepository;
    protected $catalogProductCompareList;
    protected $customerVisitor;
    protected $customerSession;
    protected $storeManager;

    /**
     * AddCompare constructor.
     *
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
     * @param Data $jsonEncode
     * @param AjaxcompareData $ajaxCompareHelper
     * @param Registry $registry
     * @param ListCompare $catalogProductCompareList
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Data $jsonEncode,
        AjaxcompareData $ajaxCompareHelper,
        Session $customerSession,
        Registry $registry,
        ListCompare $catalogProductCompareList,
        StoreManagerInterface $storeManager,
        Visitor $customerVisitor
    )
    {
        parent::__construct(
            $context
        );
        $this->_ajaxCompareHelper = $ajaxCompareHelper;
        $this->_jsonEncode = $jsonEncode;
        $this->_coreRegistry = $registry;
        $this->productRepository = $productRepository;
        $this->catalogProductCompareList = $catalogProductCompareList;
        $this->customerVisitor = $customerVisitor;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;

    }

    /**
     * Add item to compare list
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = [];
        $productId = $this->getRequest()->getParam('product');
        if ($productId && ($this->customerVisitor->getId() || $this->customerSession->isLoggedIn())) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
            if ($product) {
                $this->catalogProductCompareList->addProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                if ($this->getRequest()->getParam('isCompare') == true) {
                    $this->_coreRegistry->register('product', $product);
                    $this->_coreRegistry->register('current_product', $product);

                    $htmlPopup = $this->_ajaxCompareHelper->getSuccessHtml();
                    $result['success'] = true;
                    $result['html_popup'] = $htmlPopup;
                } else {
                    $htmlPopup = $this->_ajaxCompareHelper->getErrorHtml();
                    $result['success'] = false;
                    $result['html_popup'] = $htmlPopup;
                }
            }
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
        }
        return $this->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
    }
}
