<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Shopbybrand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
//    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
//    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
//    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
//    protected $_objectConverter;

    /**
     * @var \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory
     */
    protected $_storeValueCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
//        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
//        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
//        \Magento\Framework\Convert\DataObject $objectConverter,
//        \Magento\Store\Model\System\Store $systemStore,
        \Magestore\Shopbybrand\Model\ResourceModel\StoreValue\CollectionFactory $storeValueCollectionFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
//        $this->_systemStore = $systemStore;
//        $this->_groupRepository = $groupRepository;
//        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
//        $this->_objectConverter = $objectConverter;
        $this->_storeValueCollectionFactory = $storeValueCollectionFactory;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
//    protected function _prepareLayout()
//    {
        //parent::_prepareLayout();
//        \Magento\Framework\Data\Form::setElementRenderer(
//            $this->getLayout()->createBlock(
//                'Magento\Backend\Block\Widget\Form\Renderer\Element',
//                $this->getNameInLayout() . '_element'
//            )
//        );
//        \Magento\Framework\Data\Form::setFieldsetRenderer(
//            $this->getLayout()->createBlock(
//                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset',
//                $this->getNameInLayout() . '_fieldset'
//            )
//        );
//        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
//            $this->getLayout()->createBlock(
//                'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element',
//                $this->getNameInLayout() . '_fieldset_element'
//            )
//        );

//        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
//            $this->getLayout()->createBlock(
//                'Magestore\Shopbybrand\Block\Adminhtml\Widget\Form\Renderer\Fieldset\Element',
//                $this->getNameInLayout() . '_fieldset_element'
//            )
//        );
//        return $this;
//    }

    public function _beforeToHtml()
    {
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magestore\Shopbybrand\Block\Adminhtml\Widget\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_custom_fieldset_element'
            )
        );

        parent::_beforeToHtml();
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('registry_model');


        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('brand_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $elements = [] ;
        $elements['brand_name'] = $fieldset->addField(
            'brand_name',
            'text',
            ['name' => 'brand_name', 'label' => __('Brand Name'), 'title' => __('Brand Name'), 'required' => true]
        );

        $elements['title'] = $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Page Title'), 'title' => __('Page Title')]
        );

        $elements['url_key'] = $fieldset->addField(
            'url_key',
            'text',
            [ 'name' => 'url_key', 'label' => __('Url Key'), 'title' => __('Url Key'), 'required' => true]
        );
        $wysiwygConfig = $this->_wysiwygConfig->getConfig();

        $elements['short_description'] = $fieldset->addField(
            'short_description',
            'editor',
            [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'config' => $wysiwygConfig
            ]
        );

        $elements['description'] = $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;',
                'config' => $wysiwygConfig
            ]
        );

        $elements['meta_keywords'] = $fieldset->addField(
            'meta_keywords',
            'text',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );

        $elements['meta_description'] = $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Descriptions'),
                'title' => __('Meta Descriptions'),
                'style' => 'height: 100px;'
            ]
        );

        $elements['is_featured'] = $fieldset->addField(
            'is_featured',
            'select',
            [
                'label' => __('Is Featured'),
                'title' => __('Is Featured'),
                'name' => 'is_featured',
                'required' => true,
                'options' => ['1' => __('Yes'), '2' => __('No')]
            ]
        );

        $elements['is_active'] = $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Active'), '2' => __('Inactive')]
            ]
        );

        $elements['logo'] = $fieldset->addField(
            'logo',
            'image',
            [
                'title' => __('Logo'),
                'label' => __('Logo'),
                'name'  => 'logo',
                'note'  => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );
        $elements['banner'] = $fieldset->addField(
            'banner',
            'image',
            [
                'title' => __('Banner'),
                'label' => __('Banner'),
                'name'  => 'banner',
                'note'  => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );

        $elements['banner_url'] = $fieldset->addField(
            'banner_url',
            'text',
            [
                'name'     => 'banner_url',
                'label'    => __('Banner click-through URL'),
                'title'    => __('Banner click-through URL'),
                'required' => false,
            ]
        );

        $elements['sort_order'] = $fieldset->addField('sort_order', 'text', ['name' => 'sort_order', 'label' => __('Sort Order')]);

        $storeViewId = $this->getRequest()->getParam('store');
        if(!$storeViewId){
            $storeViewId = $this->_storeManager->getStore()->getId();
        }

        $attributesInStore = $this->_storeValueCollectionFactory
            ->create()
            ->addFieldToFilter('brand_id', $model->getId())
            ->addFieldToFilter('store_id', $storeViewId)
            ->getColumnValues('code');

        foreach ($attributesInStore as $attribute) {
            //$elements[$attribute]->setStoreId($storeViewId);
        }


        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
