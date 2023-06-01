<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\RequestProductReviewInterface;
use Magento\Framework\DataObject;

/**
 * Class RequestProductReview
 */
class RequestProductReview extends DataObject implements RequestProductReviewInterface
{

    /**
     * 
     * @return int
     */
    public function getPrice()
    {
        return $this->_getData('price');
    }

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData('price', $price);
    }

    /**
     * 
     * @return int
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData('value', $value);
    }

    /**
     * 
     * @return int
     */
    public function getQuality()
    {
        return $this->_getData('quality');
    }

    /**
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        return $this->setData('quality', $quality);
    }
}
