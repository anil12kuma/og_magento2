<?php
namespace Dev\RestApi\Api;

interface BannerInterface
{
    /**
     * @return int
     */
    public function getBannerId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return int
     */
    public function getType();

    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getImageUrl();

    /**
     * @return string
     */
    public function getUrlBanner();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getNewtab();
}