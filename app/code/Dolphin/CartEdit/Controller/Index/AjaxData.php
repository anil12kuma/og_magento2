<?php

namespace Dolphin\CartEdit\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class AjaxData extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $request;
    protected $customOption;
    protected $_productloader;
    protected $cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Product\Option $customOption,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->customOption = $customOption;
        $this->cart = $cart;
        $this->_productloader = $_productloader;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $data = $this->request->getParams();
        $cart_item = $this->getCartItems($data['quoteid']);
        if ($cart_item === null) {
            $this->messageManager->addError(__('The quote item isn\'t found. Verify the item and try again.'));
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $data = ['content' => '', 'name' => '', "error" => 1];
            $resultJson->setData($data);
        } else {
            $block = $resultPage->getLayout()
                ->createBlock(\Dolphin\CartEdit\Block\CartEdit::class)
                ->setData("quotedata", $data)
                ->setTemplate("Dolphin_CartEdit::show_edit_popup.phtml");

            $prd_data = $this->_productloader->create()->load($data['prdid']);
            
            ///custom by sandy]
            $productbrand = '';
            if ($prd_data->getResource()->getAttribute('brand')->getFrontend()->getValue($prd_data)) {
               $productbrand = '<span style="font-weight: bold;letter-spacing: 0.5px;font-size: 12px;color: #6f6f6f;font-family: \'Overpass\';">'.$prd_data->getResource()->getAttribute('brand')->getFrontend()->getValue($prd_data).'</span><br/>';
            }
            ///custom by sandy
            $has_options = $this->hasProductCustom($prd_data);
            if ($has_options) {
                $block_default = $resultPage->getLayout()
                    ->createBlock(\Magento\Catalog\Block\Product\View\Options\Type\DefaultType::class)
                    ->setTemplate('Magento_Catalog::product/view/options/type/default.phtml');
                $block_text = $resultPage->getLayout()
                    ->createBlock(\Magento\Catalog\Block\Product\View\Options\Type\Text::class)
                    ->setTemplate('Magento_Catalog::product/view/options/type/text.phtml');
                $block_file = $resultPage->getLayout()
                    ->createBlock(\Magento\Catalog\Block\Product\View\Options\Type\File::class)
                    ->setTemplate('Magento_Catalog::product/view/options/type/file.phtml');
                $block_select = $resultPage->getLayout()
                    ->createBlock(\Magento\Catalog\Block\Product\View\Options\Type\Select::class)
                    ->setTemplate('Magento_Catalog::product/view/options/type/select.phtml');
                $block->setChild('default', $block_default);
                $block->setChild('text', $block_text);
                $block->setChild('file', $block_file);
                $block->setChild('select', $block_select);
            }
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $data = ['content' => $block->toHtml(), 'name' => $productbrand.$prd_data->getName(), "error" => 0];
            $resultJson->setData($data);
        }
        return $resultJson;
    }

    public function hasProductCustom($prd_data)
    {
        $has_option = 0;
        $custom_options = $this->customOption->getProductOptionCollection($prd_data);
        if (!empty($custom_options->getData())) {
            $has_option = 1;
        }
        return $has_option;
    }

    public function getCartItems($cart_id)
    {
        $cart_data = $this->cart->getQuote()->getAllVisibleItems();
        foreach ($cart_data as $item) {
            if ($cart_id == $item->getId()) {
                return $item;
            }
        }
    }
}
