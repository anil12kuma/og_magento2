<?php
namespace Dev\RestApi\Api;

interface RequestProductReviewInterface
{
    /**
     * @return int
     */
    public function getPrice();

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getValue();

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return int
     */
    public function getQuality();

    /**
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality);

}
