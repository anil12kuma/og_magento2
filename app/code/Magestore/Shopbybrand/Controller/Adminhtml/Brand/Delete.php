<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Controller\Adminhtml\Brand;

use Magestore\Shopbybrand\Controller\Adminhtml\Brand;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magestore\Shopbybrand\Model\BrandProducts;

class Delete extends Brand
{

    /**
     * @var \Magestore\Shopbybrand\Model\Brandproducts
     */
    protected $_brandProducts;
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        BrandProducts $brandProducts
    ) {
        parent::__construct($context);
        $this->_brandProducts = $brandProducts;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Magestore\Shopbybrand\Model\Brand $model */
                $model = $this->_objectManager->create('Magestore\Shopbybrand\Model\Brand');
                $model->load($id);
                $brand_name = $model->getData('brand_name');
                $model->deleteUrlRewrite();
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the brand.'));

                $this->_brandProducts->deleteProductData($id);

                /* delete attribute option - Anthony */
                $attributeHelper = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Attribute');
                $attributeHelper->deleteOptions($brand_name);
                /* end delete attribute option - Anthony */

                $this->_redirect('shopbybrand/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this brand right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('shopbybrand/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a brand to delete.'));
        $this->_redirect('shopbybrand/*/');
    }
}
