<?php

namespace Meetanshi\IndianGst\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Model\Product;

class Hsncode extends Column
{
    protected $resourceConnection;
    protected $product;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Product $product,
        array $components = [],
        array $data = [])
    {
        $this->resourceConnection = $resourceConnection;
        $this->product = $product;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['product_id'])) {
                    $product = $this->product->load($item['product_id']);
                    $hsn = $product->getHsnCode();
                    $item['hsn_code'] = $hsn;
                }
            }
        }
        return $dataSource;
    }
}