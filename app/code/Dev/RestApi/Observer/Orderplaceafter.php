<?php

namespace Dev\RestApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;


class Orderplaceafter implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;
    protected $logger;

    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;



    public function __construct(
        \Magento\Sales\Model\Order $order,
        LoggerInterface $logger,
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = [];
        $order = $observer->getEvent()->getOrder();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
        $order_no = $order->getIncrementId();
        $itemCollection = $order->getItemsCollection();
        $items = $itemCollection->getData();
        $this->logger->info('Event trigger orderplaceafter4: ');
        $this->logger->info('Event trigger orderplaceafter4: ' . print_r($items, true));
        foreach ($items as $key => $item) {
            if (empty($item['parent_item_id'])) {
                $product = $productRepository->get($item['sku']);
                $data[$product->getAttributeText('brand')][$key]['product_id'] = $item['product_id'];
                $data[$product->getAttributeText('brand')][$key]['order_no'] = $order_no;
                $data[$product->getAttributeText('brand')][$key]['order_date'] = $item['created_at'];
                $data[$product->getAttributeText('brand')][$key]['name'] = $product->getData('name');
                $data[$product->getAttributeText('brand')][$key]['sku'] = $item['sku'];
                $data[$product->getAttributeText('brand')][$key]['qty_ordered'] = $item['qty_ordered'];
                $data[$product->getAttributeText('brand')][$key]['mrp'] = $item['original_price'];
                $data[$product->getAttributeText('brand')][$key]['selling_price'] = $item['price_incl_tax'];
                $data[$product->getAttributeText('brand')] = array_values($data[$product->getAttributeText('brand')]);

                //convert data in html
                // $data.='<p>Product Id: '.$item['product_id'].'</p>';
                // $data.='<p>Order No: '.$order_no.'</p>';
                // $data.='<p>Order Date: '.$item['created_at'].'</p>';
                // $data.='<p>Name: '.$product->getData('name').'</p>';
                // $data.='<p>Sku: '.$item['sku'].'</p>';
                // $data.='<p>Qty Ordered: '.$item['qty_ordered'].'</p>';
                // $data.='<p>Mrp: '.$item['original_price'].'</p>';
                // $data.='<p>Selling Price: '.$item['price_incl_tax'].'</p>';
                // $data.='<p>Brand: '.$product->getAttributeText('brand').'</p>';

            }
        }
        // $data = array_values($data);
        $this->logger->info('Event trigger orderplaceaftertest1: ' . print_r($data, true));
        // $data = json_encode($data);
        // $this->logger->info('Event trigger orderplaceaftertest2: ' . $data);
        // $this->logger->info('Event trigger orderplaceaftertest: ' . $items);


        

        foreach ($data as $key => $value) {
                $templateId = 'my_custom_email_template'; // template id
                $fromEmail = 'orders@outdoorgoats.com'; // sender Email id
                $fromName = 'OutdoorGoats'; // sender Name
                $toEmail = '';
            if ($key === 'Trek n Ride' || $key === 'Athlos' || $key === 'cocopalm') {
                $toEmail = 'sahil.cheulkar@outdoorgoats.com'; // receiver email id
            }else if($key === 'Baller Athletik' || $key === 'Reccy'){
                $toEmail = 'khyati@outdoorgoats.com'; // receiver email id
            }else if($key === 'Kue' || $key === 'Tripole'){
                $toEmail = 'vikhil.machado@outdoorgoats.com'; // receiver email id
            }else if($key === 'Gokyo' || $key === 'Trekmonk' || $key === 'River' || $key === 'Blue Bolt'){
                $toEmail = 'prashant.pandav@outdoorgoats.com';
            }else{
                $toEmail = 'joanne@outdoorgoats.com';
            }

            $emailData = "";

            try {
                    $emailData .= "<h6>OutdoorGoats</h6>";
                    $emailData .= "<h3>" . $key . "</h3>";
                foreach ($value as $val) {
                    
                    $emailData .= "<p>Product Id: " . $val['product_id'] . "</p>";
                    $emailData .= "<p>Order No: " . $val['order_no'] . "</p>";
                    $emailData .= "<p>Order Date: " . $val['order_date'] . "</p>";
                    $emailData .= "<p>Name: " . $val['name'] . "</p>";
                    $emailData .= "<p>Sku: " . $val['sku'] . "</p>";
                    $emailData .= "<p>Qty Ordered: " . $val['qty_ordered'] . "</p>";
                    $emailData .= "<p>Mrp: " . $val['mrp'] . "</p>";
                    $emailData .= "<p>Selling Price: " . $val['selling_price'] . "</p>";
                    $emailData .= "<br/>";
                    

                    // $emailData = json_encode($emailData);

                    
                }

                $templateVars = [
                    'name' => $key,
                    'data' => $emailData,
                ];
                
                $storeId = $this->storeManager->getStore()->getId();

                $from = ['email' => $fromEmail, 'name' => $fromName];
                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => $storeId
                ];
                $transport = $this->transportBuilder->setTemplateIdentifier('brand_email', $storeScope)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmail)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $transport = json_encode($transport);
                // $this->logger->info('Event trigger orderplaceafter4 try: ' . $transport);

            } catch (\Exception $e) {
                $this->logger->info('Event trigger orderplaceafter4: Catch');
                $this->logger->info($e->getMessage());
            }
        }
    }
}
