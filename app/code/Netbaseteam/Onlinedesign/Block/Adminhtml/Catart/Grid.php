<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Catart;

use Netbaseteam\Onlinedesign\Model\Catart;

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
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Catart\CollectionFactory $collectionFactory,
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
		$this->setId('catartGrid');
		$this->setDefaultSort('cat_id');
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
		$this->addColumn('cat_id', [
			'header' => __('ID'),
			'index' => 'cat_id',
		]);

		$this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);

		// $this->addColumn('status', array(
		// 	'header' => __('Status'),
		// 	'align' => 'left',
		// 	'index' => 'status',
		// 	'type' => 'options',
		// 	'options' => Catart::getAvailableStatuses(),
		// ));

		$this->addColumn(
			'action',
			[
				'header' => __('Action'),
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
						'field' => 'cat_id',
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
		$this->setMassactionIdField('cat_id');
		$this->getMassactionBlock()->setFormFieldName('catart');

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
		return $this->getUrl('*/*/edit', ['cat_id' => $row->getId()]);
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
