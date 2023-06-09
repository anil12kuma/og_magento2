<?php
namespace W3speedster\Optimization\Helper;

use Magento\Framework\App\Helper;


class Cache extends Helper\AbstractHelper
{
    const CACHE_TAG = 'OPTIMIZATION';
    const CACHE_ID = 'optimization';
    const CACHE_LIFETIME = 86400;

    protected $cache;
    protected $cacheState;
    protected $storeManager;
    private $storeId;
   
    public function __construct(
        Helper\Context $context,
        \Magento\Framework\App\Cache $cache,
        \Magento\Framework\App\Cache\State $cacheState,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->storeManager = $storeManager;
        $this->storeId = $storeManager->getStore()->getId();
        parent::__construct($context);
    }
   
    public function getId($method, $vars = array())
    {
        return base64_encode($this->storeId . self::CACHE_ID . $method . implode('', $vars));
    }

    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            return $this->cache->load($cacheId);
        }

        return FALSE;
    }

    public function save($data, $cacheId, $cacheLifetime = self::CACHE_LIFETIME)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            $this->cache->save($data, $cacheId, array(self::CACHE_TAG), $cacheLifetime);
            return TRUE;
        }
        return FALSE;
    }
}

?>