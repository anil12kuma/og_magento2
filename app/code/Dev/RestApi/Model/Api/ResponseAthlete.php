<?php
namespace Dev\RestApi\Model\Api;
use Dev\RestApi\Api\ResponseAthleteInterface;
use Magento\Framework\DataObject;

/**
 * Class ResponseAthlete
 */
class ResponseAthlete extends DataObject implements ResponseAthleteInterface
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
     * Get attributes list.
     *
     * @return \Dev\RestApi\Api\ResponseAthleteDetailsInterface[]
     */
    public function getItems()
    {
        return $this->_getData('data');
    }

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\ResponseAthleteDetailsInterface[] $data
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
