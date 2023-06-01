<?php
namespace Dev\RestApi\Api;

/**
 * Interface ResponseSliderInterface
 * 
 */
interface ResponseSliderInterface
{
    /**
     * @return Dev\RestApi\Api\BannerInterface[]
     */
    public function getBanners();

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\BannerInterface[] $data
     * @return $this
     */
    public function setBanners(array $data);

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * Set total count.
     *
     * @param int $total_count
     * @return $this
     */
    public function setTotalCount($total_count);
}