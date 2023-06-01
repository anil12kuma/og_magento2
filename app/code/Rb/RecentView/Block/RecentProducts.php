<?php
namespace Rb\RecentView\Block;
class RecentProducts extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Reports\Block\Product\Viewed
     */
    protected $recentlyViewed;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Reports\Block\Product\Viewed            $recentlyViewed
     * @param array                                            $data
     */
    public function __construct(
       \Magento\Framework\View\Element\Template\Context $context,
       \Magento\Reports\Block\Product\Viewed $recentlyViewed,
       array $data = []
    ) {
       $this->recentlyViewed = $recentlyViewed;
       parent::__construct($context, $data);
    }
    /**
     * Get Collection Recently Viewed product
     * @return mixed
     */
    public function getRecentViewCollection()
    {
       return $this->recentlyViewed->getItemsCollection()->load();
    }
}
?>