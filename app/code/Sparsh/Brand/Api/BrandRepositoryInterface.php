<?php

namespace Sparsh\Brand\Api;

/**
 * Interface BrandRepositoryInterface
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
interface BrandRepositoryInterface
{
    /**
     * Save Brand.
     *
     * @param \Sparsh\Brand\Api\Data\BrandInterface $brand
     * @return \Sparsh\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Sparsh\Brand\Api\Data\BrandInterface $brand);

    /**
     * Retrieve Brand.
     *
     * @param int $brandId
     * @return \Sparsh\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($brandId);

    /**
     * Delete Brand.
     *
     * @param \Sparsh\Brand\Api\Data\BrandInterface $brand
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Sparsh\Brand\Api\Data\BrandInterface $brand);

    /**
     * Delete Brand by ID.
     *
     * @param int $brandId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($brandId);
}
