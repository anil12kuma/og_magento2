<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Api;

/**
 * @api
 */
interface CancelOrderRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\CancelOrder\Api\Data\CancelOrderInterface $cancelOrder
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     */
    public function save(\Amasty\CancelOrder\Api\Data\CancelOrderInterface $cancelOrder);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\CancelOrder\Api\Data\CancelOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\CancelOrder\Api\Data\CancelOrderInterface $cancelOrder
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CancelOrder\Api\Data\CancelOrderInterface $cancelOrder);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
