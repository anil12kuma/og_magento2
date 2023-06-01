<?php
namespace Gearup\Athelete\Block\Adminhtml\Athelete;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Gearup\Athelete\Model\atheleteFactory
     */
    protected $_atheleteFactory;

    /**
     * @var \Gearup\Athelete\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Gearup\Athelete\Model\atheleteFactory $atheleteFactory
     * @param \Gearup\Athelete\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Gearup\Athelete\Model\AtheleteFactory $AtheleteFactory,
        \Gearup\Athelete\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_atheleteFactory = $AtheleteFactory;
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
        $collection = $this->_atheleteFactory->create()->getCollection();
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
					'activities',
					[
						'header' => __('Activities'),
						'index' => 'activities',
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
		

		
		   $this->addExportType($this->getUrl('athelete/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('athelete/*/exportExcel', ['_current' => true]),__('Excel XML'));

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
        //$this->getMassactionBlock()->setTemplate('Gearup_Athelete::athelete/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('athelete');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('athelete/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('athelete/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('athelete/*/index', ['_current' => true]);
    }

    /**
     * @param \Gearup\Athelete\Model\athelete|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'athelete/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	

}