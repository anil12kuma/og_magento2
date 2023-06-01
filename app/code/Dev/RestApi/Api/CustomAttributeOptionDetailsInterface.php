<?php
namespace Dev\RestApi\Api;

interface CustomAttributeOptionDetailsInterface
{

    /**
     * Option sku
     *
     * @return string
     */
    public function getNameSku();

    /**
     * Set option sku
     *
     * @param string $sku
     * @return $this
     */
    public function setNameSku($sku);

    /**
     * Option name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set option name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

}
