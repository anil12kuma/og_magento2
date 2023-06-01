<?php
namespace W3speedster\Optimization\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\App\Action\Action;
/**
 * System / Cache Management / Cache type "Your Cache Type Label"
 */
class CacheType extends TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'optimization';

    /**
     * The tag name that limits the cache cleaning scope within a particular tag
     */
    const CACHE_TAG = 'OPTIMIZATION';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }	
    
}

