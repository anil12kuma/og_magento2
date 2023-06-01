<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * RuleActions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $viewLink = $this->urlBuilder->getUrl(
                    'sales/order/view',
                    ['order_id' => $item['order_id']]
                );

                if (isset($item['increment_id'])) {
                    $item['increment_id'] = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $viewLink,
                        '#' . $item['increment_id']
                    );
                }

                $item[$name]['view'] = [
                    'href'  => $viewLink,
                    'label' => __('View order')
                ];
            }
        }

        return $dataSource;
    }
}
