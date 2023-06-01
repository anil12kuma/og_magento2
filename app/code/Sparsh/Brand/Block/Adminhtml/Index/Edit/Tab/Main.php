<?php
/**
 * Class Main
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Block\Adminhtml\Index\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

/**
 * Class Main
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Main extends Generic implements TabInterface
{
    /**
     * BrandModel
     *
     * @var \Sparsh\Brand\Model\Brand
     */
    protected $brand;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * Main constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context     context
     * @param \Magento\Framework\Registry             $registry    registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory formFactory
     * @param \Sparsh\Brand\Model\Brand           $brand       brand
     * @param array                                   $data        data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Sparsh\Brand\Model\Brand $brand,
        \Magento\Store\Model\System\Store $systemStore,
        CustomerGroup $customerGroup,
        array $data = []
    ) {
        $this->brand = $brand;
        $this->systemStore =$systemStore;
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     *
     * @return mixed
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('brands_index');

         $form = $this->_formFactory->create(
             [
                'data' => [
                    'id' => 'edit_form',
                    'enctype'=>'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
             ]
         );

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Athelete Details'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('brand_id', 'hidden', ['name' => 'brand_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description')
            ]
        );

        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name'     => 'store_id[]',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, true),
            ]
        );

        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
                'name'     => 'customer_group_id[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'values'   =>$this->customerGroup->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'meta_title',
            'text',
            [
                'name' => 'meta_title',
                'label' => __('SEO Meta Title'),
                'title' => __('SEO Meta Title')
            ]
        );

        $fieldset->addField(
            'meta_keywords',
            'text',
            [
                'name' => 'meta_keywords',
                'label' => __('SEO Meta Keywords'),
                'title' => __('SEO Meta Keywords')
            ]
        );

        $fieldset->addField(
            'meta_description',
            'text',
            [
                'name' => 'meta_description',
                'label' => __('SEO Meta Description'),
                'title' => __('SEO Meta Description')
            ]
        );

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key')
            ]
        );

        if ($model->getId()) {
            $isRequired=false;
        } else {
            $isRequired=true;
        }

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'note' => __(
                    'Note : Please upload image of 210 x 50 (Width x Height) size.'
                ),
                'class' =>'admin__control-image',
                'required' => $isRequired,
            ]
        )->setAfterElementHtml(
            '<script>
                require([
                    "jquery",
                ], function($){
                    $(document).ready(function () {
                        $("#post_image_delete").parent().hide();
                        if($("#post_image_image").attr("src")){
                            $("#post_image").removeClass("required-file");
                        }else{
                            $("#post_image").addClass("required-file");
                        }

                        $( "#post_image" ).attr(
                            "accept", 
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
            </script>'
        );

        $fieldset->addField(
            'banner_image',
            'image',
            [
                'name' => 'banner_image',
                'label' => __('Banner Image'),
                'title' => __('Banner Image'),
                'class' =>'admin__control-image'
            ]
        )->setAfterElementHtml(
            '<script>
                require([
                    "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#post_banner_image" ).attr(
                            "accept", 
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
            </script>'
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'class'     => 'validate-number',
                'required' => true
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'values' => $this->brand->getAvailableStatuses()
            ]
        );

        if (!$model->getId()) {
            $model->setData('status', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Athelete Details');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Athelete Details');
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId resourceId
     *
     * @return boolean
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
