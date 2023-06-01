<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ResponseItemListInterface;
use Magento\Framework\DataObject;

/**
 * Class ResponseItemList
 */
class ResponseItemList extends DataObject implements ResponseItemListInterface
{

    /**
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        return $this->setData('title', $title);
    }

    /**
     * 
     * @return string
     */
    public function getCategoryId()
    {
        return $this->_getData('id');
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setCategoryId(string $id = '')
    {
        return $this->setData('id', $id);
    }

    /**
     * 
     * @return string
     */
    public function getBrandId()
    {
        return $this->_getData('brand_id');
    }

    /**
     * @param string $brand_id
     * @return $this
     */
    public function setBrandId(string $brand_id = '')
    {
        return $this->setData('brand_id', $brand_id);
    }

    /**
     * Get attributes list.
     *
     * @return \Dev\RestApi\Api\ProductItemListInterface[]
     */
    public function getItems()
    {
        return $this->_getData('data');
    }

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\ProductItemListInterface[] $data
     * @return $this
     */
    public function setItems(array $data)
    {
        return $this->setData('data', $data);
    }

    /**
     * 
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_getData('totalCount');
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount(int $totalCount)
    {
        return $this->setData('totalCount', $totalCount);
    }
}
