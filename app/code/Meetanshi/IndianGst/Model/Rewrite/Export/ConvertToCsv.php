<?php

namespace Meetanshi\IndianGst\Model\Rewrite\Export;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filesystem;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as CoreConvertToCsv;
use Magento\Ui\Model\Export\MetadataProvider;

class ConvertToCsv extends CoreConvertToCsv
{
    protected $searchCriteria;
    protected $orderRepository;
    protected $countryFactory;
    protected $metadataProvider;
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        Order $order,
        CountryFactory $countryFactory,
        SearchCriteriaBuilder $criteria,
        OrderRepositoryInterface $orderRepository,
        $pageSize = 200
    ) {
        $this->searchCriteria  = $criteria;
        $this->orderRepository = $orderRepository;
        $this->countryFactory = $countryFactory;
        $this->metadataProvider = $metadataProvider;
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = hash('SHA512', microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        $this->pageSize = $totalCount;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setPageSize($this->pageSize);
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
                if ($component->getName()=='indiangst_item_grid_listing') {
                    $searchCriteria = $this->searchCriteria->addFilter(
                        OrderInterface::ENTITY_ID,
                        $item->getOrderId()
                    )->create();
                    $result = $this->orderRepository->getList($searchCriteria);
                    if (empty($result->getItems())) {
                        $item->setData('customer_name', 'No such order.');
                        $item->setData('billing_address', 'No such order.');
                        $item->setData('customer_number', 'No such order.');
                        $item->setData('customer_email', 'No such order.');
                        $item->setData('state', 'No such order.');
                        $item->setData('increment', 'No such order.');
                    } else {
                        $order = $this->orderRepository->get($item->getOrderId());
                        $billingAddress = $order->getBillingAddress();
                        $item->setData('customer_name', $billingAddress['firstname'] . ' ' . $billingAddress['lastname']);
                        $item->setData('increment', (string)$order->getData('increment_id'));
                        $item->setData('billing_address', $billingAddress['street'] . ' ' . $billingAddress['city']);
                        $item->setData('customer_number', $billingAddress['telephone']);
                        $item->setData('customer_email', $order->getData('customer_email'));
                        $item->setData('state', $billingAddress['region']);
                    }
                }
                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
