<?php
namespace Meetanshi\IndianGst\Model\ResourceModel\Item;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * @param EntityFactoryInterface $entityFactory,
     * @param LoggerInterface        $logger,
     * @param FetchStrategyInterface $fetchStrategy,
     * @param ManagerInterface       $eventManager,
     * @param StoreManagerInterface  $storeManager,
     * @param AdapterInterface       $connection,
     * @param AbstractDb             $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init('Meetanshi\IndianGst\Model\Item', 'Meetanshi\IndianGst\Model\ResourceModel\Item');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }
    protected function _construct()
    {
        parent::_construct();
        $this->addFilterToMap(
            'created_at',
            'main_table.created_at'
        );
        $this->addFilterToMap(
            'cgst_amount',
            'main_table.cgst_amount'
        );
        $this->addFilterToMap(
            'sgst_amount',
            'main_table.sgst_amount'
        );
        $this->addFilterToMap(
            'utgst_amount',
            'main_table.utgst_amount'
        );
        $this->addFilterToMap(
            'igst_amount',
            'main_table.igst_amount'
        );
        $this->addFilterToMap(
            'status',
            'order_table.status'
        );
        $this->addFilterToMap(
            'increment_id',
            'order_table.increment_id'
        );
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->where('main_table.parent_item_id IS NULL');
        $this->getSelect()->join(
            ['order_table' => $this->getTable('sales_order')],
            'main_table.order_id = order_table.entity_id',
            ['cname' => "CONCAT(customer_firstname, ' ', customer_lastname)", "customer_email", "increment_id", "status", 'order_currency_code', 'discount_amount']
        )->join(
            ['sales_order_address' => $this->getTable('sales_order_address')],
            'sales_order_address.entity_id = order_table.billing_address_id ',
            ['*','billing_address' => "CONCAT(street, ' ', city)",'cname' => "CONCAT(firstname, ' ', lastname)"]
        );
    }
}
