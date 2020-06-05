<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Color;

/**
 * Adminhtml Onlinedesign grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {
	/**
	 * @var \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory
	 */
	protected $_collectionFactory;

	/**
	 * @var \Netbaseteam\Onlinedesign\Model\Onlinedesign
	 */
	protected $_onlinedesign;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Helper\Data $backendHelper
	 * @param \Netbaseteam\Onlinedesign\Model\Onlinedesign $onlinedesignPage
	 * @param \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $collectionFactory
	 * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Netbaseteam\Onlinedesign\Model\Onlinedesign $onlinedesign,
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Color\CollectionFactory $collectionFactory,
		array $data = []
	) {
		$this->_collectionFactory = $collectionFactory;
		$this->_onlinedesign = $onlinedesign;
		parent::__construct($context, $backendHelper, $data);
	}

	/**
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->setId('colorGrid');
		$this->setDefaultSort('color_id');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Prepare collection
	 *
	 * @return \Magento\Backend\Block\Widget\Grid
	 */
	protected function _prepareCollection() {
		$collection = $this->_collectionFactory->create();
		/* @var $collection \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection */
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	/**
	 * Prepare columns
	 *
	 * @return \Magento\Backend\Block\Widget\Grid\Extended
	 */
	protected function _prepareColumns() {
		/* $this->addColumn('color_id', [
			            'header'    => __('ID'),
			            'index'     => 'color_id',
						'width'     => '50px',
						'align'	=> 'center',
		*/

		$this->addColumn('color_name', ['header' => __('Name'), 'index' => 'color_name']);

		$this->addColumn('hex', ['header' => __('Hex'), 'index' => 'hex']);

		$this->addColumn(
			'image',
			[
				'header' => __('Color'),
				'index' => 'image',
				'align' => 'center',
				'width' => '250px',
				'header_css_class' => 'col-id',
				'column_css_class' => 'col-id',
				'filter' => false,
				'sortable' => false,
				'renderer' => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Imagecolor',
			]
		);

		$this->addColumn(
			'action',
			[
				'header' => __('Edit'),
				'type' => 'action',
				'align' => 'center',
				'getter' => 'getId',
				'actions' => [
					[
						'caption' => __('Edit'),
						'url' => [
							'base' => '*/*/edit',
							'params' => ['store' => $this->getRequest()->getParam('store')],
						],
						'field' => 'color_id',
					],
				],
				'sortable' => false,
				'filter' => false,
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action',
			]
		);

		return parent::_prepareColumns();
	}

	/**
	 * @return $this
	 */
	protected function _prepareMassaction() {
		$this->setMassactionIdField('color_id');
		$this->getMassactionBlock()->setFormFieldName('colorid');

		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('onlinedesign/*/massDelete'),
				'confirm' => __('Are you sure?'),
			]
		);

		return $this;
	}

	/**
	 * Row click url
	 *
	 * @param \Magento\Framework\Object $row
	 * @return string
	 */
	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', ['color_id' => $row->getId()]);
	}

	/**
	 * Get grid url
	 *
	 * @return string
	 */
	public function getGridUrl() {
		return $this->getUrl('*/*/grid', ['_current' => true]);
	}
}
