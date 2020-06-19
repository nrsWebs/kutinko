<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Font;

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
	protected $_catCollection;

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
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Catfont\CollectionFactory $catCollection,
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Font\CollectionFactory $collectionFactory,
		array $data = []
	) {
		$this->_collectionFactory = $collectionFactory;
		$this->_onlinedesign = $onlinedesign;
		$this->_catCollection = $catCollection;
		parent::__construct($context, $backendHelper, $data);
	}

	/**
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->setId('fontGrid');
		$this->setDefaultSort('font_id');
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
		$this->addColumn('font_id', [
			'header' => __('ID'),
			'index' => 'font_id',
			'align' => 'center',
		]);

		$this->addColumn(
			'image',
			[
				'header' => __('Preview'),
				'index' => 'image',
				'align' => 'center',
				'header_css_class' => 'col-id',
				'column_css_class' => 'col-id',
				'filter' => false,
				'sortable' => false,
				'renderer' => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Imagefont',
			]
		);

		$this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);

		$categories = $this->_catCollection->create();

		$cate_arr = array();
		$cate_arr[""] = "-- Please select -- ";
		foreach ($categories as $cat):
			$cate_arr[$cat->getId()] = $cat->getTitle();
		endforeach;

		$this->addColumn(
			'category_name',
			[
				'header' => __('Font Category'),
				'index' => 'category',
				'type' => 'options',
				'width' => '250px',
				'options' => $cate_arr,
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
						'field' => 'font_id',
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
		$this->setMassactionIdField('font_id');
		$this->getMassactionBlock()->setFormFieldName('fontid');

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
		return $this->getUrl('*/*/edit', ['font_id' => $row->getId()]);
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
