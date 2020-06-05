<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Onlinedesign;

/**
 * Adminhtml Onlinedesign grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {
	/**
	 * @var \Magento\Catalog\Model\ProductFactory
	 */
	protected $_productFactory;
	/**
	 * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
	 */
	protected $_status;
	protected $_dataOnlinedesign;
	protected $_visibility;

	/**
	 * @var \Magento\Store\Model\WebsiteFactory
	 */
	protected $_websiteFactory;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Store\Model\WebsiteFactory $websiteFactory,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $status,
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
		\Magento\Catalog\Model\Product\Visibility $visibility,
		array $data = []
	) {
		$this->_websiteFactory = $websiteFactory;
		$this->_productFactory = $productFactory;
		$this->_status = $status;
		$this->_dataOnlinedesign = $dataOnlinedesign;
		$this->_visibility = $visibility;
		parent::__construct($context, $backendHelper, $data);
	}

	public function _construct() {
		parent::_construct();
		$this->setId('pgridGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		$collection = $this->_productFactory->create()->getCollection()
			->addAttributeToSelect('sku')
			->addAttributeToSelect('name');
		$collection->addAttributeToFilter('status', ['in' => $this->_status->getVisibleStatusIds()]);
		//comment to get all products 
		// $collection->setVisibility($this->_visibility->getVisibleInSiteIds());

		if (!$this->getRequest()->getParam('ajax')) {
			$model = $this->_dataOnlinedesign->getOnlinedesignCollection()
				->addFieldToFilter('status', 1);

			$product_ids = array();
			foreach ($model as $m) {
				$product_ids[] = $m->getProductId();
			}

			$collection->addAttributeToFilter('entity_id', array('in' => $product_ids))->load();
		}

		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$this->addColumn('entity_id', array(
			'header' => __('Product Id'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'entity_id',
			'type' => 'number',
		));

		$this->addColumn(
			'thumbnail',
			[
				'header' => __('Thumbnail'),
				'index' => 'thumbnail',
				'align' => 'center',
				'width' => '250px',
				'filter' => false,
				'sortable' => false,
				'renderer' => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Pthumbnail',
			]
		);

		$this->addColumn('name', array(
			'header' => __('Name'),
			'align' => 'left',
			'index' => 'name',
		));

		$this->addColumn('sku', array(
			'header' => __('Sku'),
			'align' => 'left',
			'index' => 'sku',
		));

		$this->addColumn('status', array(
			'header' => __('Status'),
			'align' => 'left',
			'index' => 'status',
			'type' => 'options',
			'options' => $this->_status->getOptionArray(),
		));

		$this->addColumn(
			'has_design',
			[
				'header' => __('Has Design'),
				'index' => 'has_design',
				'align' => 'center',
				'width' => '250px',
				'header_css_class' => 'col-id',
				'column_css_class' => 'col-id',
				'sortable' => false,
				'type' => 'options',
				'options' => array(
					1 => 'Has Design',
					2 => 'No Design',
				),
				'renderer' => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Hasdesign',
				'filter_condition_callback' => array($this, '_filterDesignCondition'),
			]
		);

		/* if (!$this->_storeManager->isSingleStoreMode()) {
			            $this->addColumn(
			                'store_ids',
			                [
			                    'header' => __('Store Views'),
			                    'index' => 'store_ids',
			                    'type' => 'store',
			                    'store_all' => true,
			                    'store_view' => true,
			                    'renderer'=>  'Netbaseteam\Onlinedesign\Block\Adminhtml\Slide\Edit\Tab\Renderer\Store',
			                    'filter_condition_callback' => [$this, '_filterStoreCondition']
			                ]
			            );
		*/

		$this->addColumn(
			'action',
			[
				'header' => __('Action'),
				'type' => 'action',
				'getter' => 'getId',
				'align' => 'center',
				'sortable' => false,
				'filter' => false,
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action',
				'renderer' => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Links',
			]
		);

		return parent::_prepareColumns();
	}

	/**
	 * @param \Magento\Framework\Data\Collection $collection
	 * @param \Magento\Backend\Block\Widget\Grid\Column $column
	 * @return void
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _filterStoreCondition($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addFieldToFilter('store_ids', array('finset' => $value));
	}

	/**
	 * @param $collection
	 * @param $column
	 */
	protected function _filterDesignCondition($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}

		$model = $this->_dataOnlinedesign->getOnlinedesignCollection()
			->addFieldToFilter('status', 1);

		$product_ids = array();
		foreach ($model as $m) {
			$product_ids[] = $m->getProductId();
		}

		if ($value == 1) {
			$this->getCollection()
				->addAttributeToFilter('entity_id', array('in' => $product_ids))
				->load();
		} else {
			$this->getCollection()
				->addAttributeToFilter('entity_id', array('nin' => $product_ids))
				->load();
		}

	}

	/**
	 * Row click url
	 *
	 * @param \Magento\Framework\Object $row
	 * @return string
	 */
	// public function getRowUrl($row)
	// {
	// return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
	// }

	/**
	 * Get grid url
	 *
	 * @return string
	 */
	public function getGridUrl() {
		return $this->getUrl('*/*/grid', ['_current' => true]);
	}
}
