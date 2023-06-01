<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magento\Framework\Exception\LocalizedException;
use Magestore\Shopbybrand\Controller\Adminhtml\Brand;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magestore\Shopbybrand\Model\BrandProducts;

class Save extends Brand
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var \Magestore\Shopbybrand\Model\BrandProducts
     */
    protected $_brandProducts;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
//        PageFactory $resultPageFactory
        BrandProducts $brandProducts
    ) {
        parent::__construct($context);
//        $this->resultPageFactory = $resultPageFactory;
        $this->_brandProducts = $brandProducts;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $productIds = [];
        $visibility_status = [];
        $data = [];
        $brandHelpder = $this->_objectManager->get('Magestore\Shopbybrand\Helper\Brand');
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Magento\CatalogRule\Model\Rule $model */
                $model = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand');
                $data = $this->getRequest()->getPostValue();
                $data['current_store_id'] = (int)$this->getRequest()->getParam('store');
                $inputFilter = new \Zend_Filter_Input(
                    [],[],$data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');

                if ($id) {
                    $model->load($id);

                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('Wrong brand specified.'));
                    }
                    $data['option_id'] = $model->getOptionId();

                }

                if (isset($data['url_key']) && $data['url_key'] != '') {
                    $data['url_key'] = $brandHelpder->refineUrlKey($data['url_key']);
                    $urlRewrite = $this->_objectManager->create('Magestore\Shopbybrand\Model\Urlrewrite')->loadByRequestPath($data['url_key'], $data['current_store_id']);
                    if ($urlRewrite->getId()) {
                        if (!$this->getRequest()->getParam('id')) {
                            $this->messageManager->addError('Url key has existed. Please fill out a valid one.');
                            $this->_getSession()->setFormData($data);
                            return $this->_redirect('shopbybrand/*/edit', ['id' => $model->getId(), 'store' => $data['current_store_id']]);
                        } elseif ($this->getRequest()->getParam('id') && $urlRewrite->getTargetPath() != 'brand/index/viewbrand/brand_id/' . $this->getRequest()->getParam('id')
                        ) {
                            $this->messageManager->addError('URL key has already existed. Please choose a different one.');
                            $this->_getSession()->setFormData($data);
                            return $this->_redirect('shopbybrand/*/edit', ['id' => $model->getId(), 'store' => $data['current_store_id']]);
                        }
                    }
                }

                if (isset($data['products_data'])) {
                    if (is_string($data['products_data'])) {
                        parse_str($data['products_data'], $productData);
                        foreach ($productData as $key => $value){
                            parse_str(base64_decode($value),$decode_data);
                            if(isset($decode_data['visibility_status']))
                                $visibility_status[$key] = $decode_data['visibility_status'];
                        }
                        $productIds = array_unique(array_keys($productData));
                    }
                    $data['product_ids'] = implode(',', $productIds);
                    $data['visibility_status'] = serialize($visibility_status);

                    if (!isset($data['featuredproducts'])) {
                        $data['featuredproducts'] = [];
                    }

                    if(isset($data['id'])){
                        $this->_brandProducts->updateProductData($data['products_data'], $data['featuredproducts'], $data['id'], ( !empty($visibility_status) ? $visibility_status : [] ) );
                    }
                }
//                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
//                if ($validateResult !== true) {
//                    foreach ($validateResult as $errorMessage) {
//                        $this->messageManager->addError($errorMessage);
//                    }
//                    $this->_getSession()->setPageData($data);
//                    $this->_redirect('shopbybrand/*/edit', ['id' => $model->getId()]);
//                    return;
//                }

                /* unset data of tab solditems submit */
                unset($data['created_at']);
                unset($data['store_id']);

                $model->setData($data);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());

                $this->uploadImage($model);
                $checkUrlKeyResult = $this->checkUrlKey($model, $data);
                if ($checkUrlKeyResult !== true) {
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('shopbybrand/*/edit', ['id' => $model->getId(), 'store' => $data['current_store_id']]);
                    return;
                }

                $model->save();

                /*featured products*/
                if(!isset($data['id']) && isset($data['featuredproducts']) && $model->getId()){
                    $this->_brandProducts->updateProductData($data['products_data'], $data['featuredproducts'], $model->getId(), ( !empty($visibility_status) ? $visibility_status : [] ) );
                }

                /**/
                $model->updateUrlKey();

                if ($model->getOptionId() == null) {
                    $optionId = $this->_objectManager->create('Magestore\Shopbybrand\Model\ResourceModel\Brand')->addOption($model);
                    $model->setOptionId($optionId)->save();
                }

                if (isset($data['products_data']) && !empty($data['products_data'])) {
                    $brandHelpder->updateProductsBrand($productIds, $model, $data['current_store_id']);
                }
                /**/

                $this->messageManager->addSuccess(__('You saved the brand.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('shopbybrand/*/edit', ['id' => $model->getId(), 'store' => $data['current_store_id']]);
                    return;
                }
                $this->_redirect('shopbybrand/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the brand data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('shopbybrand/*/edit', ['id' => $this->getRequest()->getParam('id'), 'store' => $data['current_store_id']]);
                return;
            }
        }
        $this->_redirect('shopbybrand/*/');
    }



    protected function uploadImage($model)
    {
        /** @var \Magestore\Shopbybrand\Helper\Image $imageHelper */
        $imageHelper = $this->_objectManager->get('Magestore\Shopbybrand\Helper\Image');
        /* banner brand image */
        $imageHelper->mediaUploadImage(
            $model,
            'banner',
            \Magestore\Shopbybrand\Model\Brand::BASE_MEDIA_PATH
        );
        /* logo brand image */
        $imageHelper->mediaUploadImage(
            $model,
            'logo',
            \Magestore\Shopbybrand\Model\Brand::BASE_MEDIA_PATH
        );
    }

    protected function checkUrlKey(\Magento\Framework\DataObject $model,$data)
    {
        $result = true;
        $id = $this->getRequest()->getParam('id');
        $storeViewId = $this->getRequest()->getParam('store');

        if (isset($data['url_key'])) {
            $brandHelpder = $this->_objectManager->get('Magestore\Shopbybrand\Helper\Brand');
            $url_key = $brandHelpder->refineUrlKey($data['url_key']);

            $urlrewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection')
                ->addFieldToFilter('request_path', ['eq' => $url_key])
                ->addFieldToFilter('store_id', $storeViewId)
                ->getFirstItem();

            if (!empty($urlrewrite->getData())) {
                if (!$id) {
                    $this->messageManager->addError('Url key has existed. Please fill out a valid one.');
                    $result = false;
                }else if ($urlrewrite->getTargetPath() != 'brand/index/viewbrand/brand_id/' . $id) {
                    $this->messageManager->addError('URL key has already existed. Please choose a different one.');
                    $result = false;
                }
            }else{
                $model->setData('url_key', $url_key);
            }
        }else{
            $this->messageManager->addError('Url key has existed. Please fill out a valid one.');
            $result = false;
        }

        return $result;
    }

}
