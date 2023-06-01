<?php
/**
 * Interface BrandInterface
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Api\Data;

/**
 * Interface BrandInterface
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
interface BrandInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const BRAND_ID         = 'brand_id';
    const TITLE            = 'title';
    const DESCRIPTION      = 'description';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const URL_KEY          = 'url_key';
    const IMAGE            = 'image';
    const BANNER_IMAGE     = 'banner_image';
    const POSITION         = 'position';
    const STATUS           = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Get url key
     *
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Get Position
     *
     * @return string|null
     */
    public function getPosition();

    /**
     * Get image
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Get banner image
     *
     * @return string|null
     */
    public function getBannerImage();

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set Id
     *
     * @param int $id id
     *
     * @return mixed
     */
    public function setId($id);

    /**
     * Set Title
     *
     * @param string $title title
     *
     * @return mixed
     */
    public function setTitle($title);

    /**
     * Set Description
     *
     * @param string $description description
     *
     * @return mixed
     */
    public function setDescription($description);

    /**
     * Set meta title
     *
     * @param string $metaTitle metaTitle
     *
     * @return mixed
     */
    public function setMetaTitle($metaTitle);

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords metaKeywords
     *
     * @return mixed
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Set meta description
     *
     * @param string $metaDescription metaDescription
     *
     * @return mixed
     */
    public function setMetaDescription($metaDescription);

    /**
     * Set Url key
     *
     * @param string $urlKey urlKey
     *
     * @return mixed
     */
    public function setUrlKey($urlKey);

    /**
     * Set IsActive
     *
     * @param isActive $isActive isActive
     *
     * @return mixed
     */
    public function setIsActive($isActive);
}
