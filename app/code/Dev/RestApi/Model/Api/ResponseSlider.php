<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ResponseSliderInterface;
use Magento\Framework\DataObject;

/**
 * Class Slider
 */
class ResponseSlider extends DataObject implements ResponseSliderInterface
{
    /**
     * Get attributes list.
     *
     * @return \Dev\RestApi\Api\BannerInterface[]
     */
    public function getBanners()
    {
        return $this->_getData('data');
    }

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\BannerInterface[] $data
     * @return $this
     */
    public function setBanners(array $data)
    {
        return $this->setData('data', $data);
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_getData('total_count');
    }

    /**
     * Set total count.
     *
     * @param int $total_count
     * @return $this
     */
    public function setTotalCount($total_count)
    {
        return $this->setData('total_count', $total_count);
    }
}
