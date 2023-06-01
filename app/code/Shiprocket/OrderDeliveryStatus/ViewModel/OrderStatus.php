<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Shiprocket\OrderDeliveryStatus\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;
use Magento\Backend\Model\UrlInterface;

class OrderStatus extends \Magento\Framework\DataObject implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const XML_PATH_ENABLED = 'api/details/enabled';
    const XML_PATH_EMAIL = 'api/details/email';
    const XML_PATH_PASSWORD = 'api/details/password';

    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var string
     */
    protected $tokenApiUrl = 'https://apiv2.shiprocket.in/v1/external/auth/login';

    /**
     * @var string
     */
    protected $allOrdersApiUrl = 'https://apiv2.shiprocket.in/v1/external/orders';

    /**
     * @var \Magento\Marketplace\Helper\Cache
     */
    protected $cache;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * OrderStatus constructor.
     *
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        Cache $cache,
        UrlInterface $backendUrl
    ) {
        parent::__construct();
        $this->scopeConfig = $scopeConfig;
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
    }


    /**
     * Check if enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Api Email
     *
     * @return string|null
     */
    public function getApiEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Api Password
     *
     * @return string|null
     */
    public function getApiPassword()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Gets Token 
     *
     * @return string
     */
    public function getToken()
    {
        $postDataArray = [
            "email" => $this->getApiEmail(),
            "password" => $this->getApiPassword(),
        ];
        try {
            $this->getCurlClient()->post($this->tokenApiUrl, $postDataArray);
            $response = json_decode($this->getCurlClient()->getBody(), true);
            if ($response['token']) {
                $this->getCache()->savePartnersToCache($response['token']);
                return $response['token'];
            } else {
                return $this->getCache()->loadPartnersFromCache();
            }
        } catch (\Exception $e) {
            return $this->getCache()->loadPartnersFromCache();
        }
    }

    /**
     * Gets partners json
     *
     * @return array
     */
    public function getApiOrderData()
    {
        $token = $this->getToken();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->allOrdersApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer " . $token
            ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        if ($data['data']) {
            for ($i = 0; $i < count($data['data']); $i++) {

    $orderData[] = array();
                $orderData[$i][$data['data'][$i]['channel_order_id']] =$data['data'][$i]['status'] ;

                
        
            }
        }
        return $orderData;
    }

    public function showStatusByOrderId($orderId)
    {
        $token = $this->getToken();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->allOrdersApiUrl}?search={$orderId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer " . $token
            ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        $orderStatus = null;

        if ($data['data'] && array_key_exists('0', $data['data'])) {
            $orderStatus = $data['data'][0]['status'];
            
        }
        return $orderStatus;
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }


   /**
     * @return string
     */
    public function showStatus($orderId)
    {
        $apiData = $this->getApiOrderData();
      
        foreach ($apiData as $data) {
             foreach ($data as $id=> $status) {
     
            if ($id == $orderId) {
              return $status;
            } 
             }
        }
        return $status;
    }
}
