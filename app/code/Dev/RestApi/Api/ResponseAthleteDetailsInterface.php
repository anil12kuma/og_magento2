<?php
namespace Dev\RestApi\Api;

interface ResponseAthleteDetailsInterface
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
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description);

    /**
     * @return string
     */
    public function getImageUrl();

    /**
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl(string $imageUrl);

}
