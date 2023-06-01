<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Shopbybrand\Helper;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Helper Data
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;
    /**
     * EAV product attribute factory
     *
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $_moduleDataSetupInterface;


    /**
     * Attribute constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeFactory $attributeFactory
     * @param ModuleDataSetupInterface $moduleDataSetupInterface
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $attributeFactory,
        ModuleDataSetupInterface $moduleDataSetupInterface
    ) {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_moduleDataSetupInterface = $moduleDataSetupInterface;
    }

    /**
     * @return brand helper
     */
    public function getBrandHelper(){
        $_brand_helper = $this->_objectManager->create('Magestore\Shopbybrand\Helper\Brand');
        return $_brand_helper;
    }

    /**
     * delete product attribute option
     * @param $brand_name: name of brand that is deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOptions($brand_name)
    {
        try {
            $attributeCode = $this->getBrandHelper()->getAttributeCode();
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetupInterface]);
            $entityTypeId = $eavSetup->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
            $attribute = $this->_attributeFactory->create()->loadByCode($entityTypeId, $attributeCode);
            $options = $attribute->getOptions();

            $optionsToRemove = [];
            foreach($options as $option)
            {
                if ($option['value'] && strcasecmp($brand_name, $option['label']) == 0)
                {
                    $optionsToRemove['delete'][$option['value']] = true;
                    $optionsToRemove['value'][$option['value']] = true;
                }
            }
            if(sizeof($optionsToRemove) > 0)
                $eavSetup->addAttributeOption($optionsToRemove);
        } catch (Exception $ex){

        }
    }
}
