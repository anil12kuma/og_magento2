<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ProductItemListInterface;
use Magento\Framework\DataObject;

/**
 * Class ProductItemList
 */
class ProductItemList extends DataObject implements ProductItemListInterface
{

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get('id');
    }

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id){
        return $this->setData('id', $id);
    }

     /**
     * Product sku
     *
     * @return string
     */
    public function getSku(){
        return $this->_get('sku');
    }

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku){
        return $this->setData('sku', $sku);
    }

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName(){
        return $this->_get('name');
    }

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name){
        return $this->setData('name', $name);
    }

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice(){
        return $this->_get('price');
    }

    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price){
        return $this->setData('price', $price);
    }

    /**
     * Product price
     *
     * @return float|null
     */
    public function getSpecialPrice(){
        return $this->_get('special_price');
    }

    /**
     * Set product special_price
     *
     * @param float $special_price
     * @return $this
     */
    public function setSpecialPrice($special_price){
        return $this->setData('special_price', $price);
    }

    /**
     * Product brand name
     *
     * @return string|null
     */
    public function getBrandName(){
        return $this->_get('brand_name');
    }

    /**
     * Set product brand name
     *
     * @param string $brandName
     * @return $this
     */
    public function setBrandName($brandName){
        return $this->setData('brand_name', $price);
    }

    /**
     * Product image url
     *
     * @return string|null
     */
    public function getImageUrl(){
        return $this->_get('image_url');
    }

    /**
     * Set product image url
     *
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl){
        return $this->setData('image_url', $price);
    }
}
