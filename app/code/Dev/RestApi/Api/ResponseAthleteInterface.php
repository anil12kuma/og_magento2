<?php
namespace Dev\RestApi\Api;

interface ResponseAthleteInterface
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
     * Get attributes list.
     *
     * @return \Dev\RestApi\Api\ResponseAthleteDetailsInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Dev\RestApi\Api\ResponseAthleteDetailsInterface[] $data
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
