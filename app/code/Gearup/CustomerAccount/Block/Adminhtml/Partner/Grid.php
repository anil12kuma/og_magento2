<?php
namespace Gearup\Partner\Block\Adminhtml\Partner;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Gearup\Partner\Model\partnerFactory
     */
    protected $_partnerFactory;

    /**
     * @var \Gearup\Partner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Gearup\Partner\Model\partnerFactory $partnerFactory
     * @param \Gearup\Partner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Gearup\Partner\Model\PartnerFactory $PartnerFactory,
        \Gearup\Partner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_partnerFactory = $PartnerFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_partnerFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'name',
					[
						'header' => __('Name'),
						'index' => 'name',
					]
				);
				
				$this->addColumn(
					'email',
					[
						'header' => __('Email'),
						'index' => 'email',
					]
				);
				
				$this->addColumn(
					'phone',
					[
						'header' => __('Phone'),
						'index' => 'phone',
					]
				);
				
				$this->addColumn(
					'selection_area',
					[
						'header' => __('Selection Area'),
						'index' => 'selection_area',
					]
				);
				
				$this->addColumn(
					'selections',
					[
						'header' => __('Selections'),
						'index' => 'selections',
					]
				);
				
				$this->addColumn(
					'message',
					[
						'header' => __('Message'),
						'index' => 'message',
					]
				);
				


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('partner/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('partner/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Gearup_Partner::partner/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('partner');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('partner/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('partner/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('partner/*/index', ['_current' => true]);
    }

    /**
     * @param \Gearup\Partner\Model\partner|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'partner/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	

}