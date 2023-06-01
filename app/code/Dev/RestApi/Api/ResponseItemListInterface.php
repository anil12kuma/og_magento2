<?php
namespace Dev\RestApi\Api;

interface ResponseItemListInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * @return string
     */
    public function getCategoryId();

    /**
     * @param string $id
     * @return $this
     */
    public function setCategoryId(string $id = '');

    /**
     * @return string
     */
    public function getBrandId();

    /**
     * @param string $brand_id
     * @return $this
     */
    public function setBrandId(string $brand_id = '');

    /**
     * Get attributes list.
     *
     * @return \Dev\RestApi\Api\ProductItemListInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\ProductItemListInterface[] $data
     * @return $this
     */
    public function setItems(array $data);

    /**
     * 
     * @return int
     */
    public function getTotalCount();

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount(int $totalCount);
}
