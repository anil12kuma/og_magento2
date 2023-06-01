<?php
namespace Dev\RestApi\Api;

interface ProductItemListInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SKU = 'sku';

    const NAME = 'name';

    const PRICE = 'price';

    const SPECIAL_PRICE = 'special_price';

    const BRAND_NAME = 'brand_name';

    const IMAGE_URL = 'image_url';

    const ATTRIBUTES = [
        self::SKU,
        self::NAME,
        self::PRICE,
        self::SPECIAL_PRICE,
        self::IMAGE_URL,
    ];
    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Product price
     *
     * @return float|null
     */
    public function getSpecialPrice();

    /**
     * Set product special_price
     *
     * @param float $special_price
     * @return $this
     */
    public function setSpecialPrice($special_price);

    /**
     * Product brand name
     *
     * @return string|null
     */
    public function getBrandName();

    /**
     * Set product brand name
     *
     * @param string $brandName
     * @return $this
     */
    public function setBrandName($brandName);

    /**
     * Product image url
     *
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set product image url
     *
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl);

}
