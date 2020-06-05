<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Order;

/**
 * Adminhtml Onlinedesign grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $_dataOnlinedesign;
	protected $_orderCollectionFactory;
  
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
		array $data = []
	) {
		$this->_dataOnlinedesign = $dataOnlinedesign;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		parent::__construct($context, $backendHelper, $data);
	}

	public function _construct()
	{
	  parent::_construct();
	  $this->setId('orderGrid');
	  $this->setDefaultSort('entity_id');
	  $this->setDefaultDir('DESC');
	  $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = $this->_orderCollectionFactory->create()
						->addAttributeToSelect('*')
						->setOrder('entity_id','DESC');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
	  $this->addColumn('increment_id', array(
		  'header'    => __('Order #'),
		  'align'     => 'center',
		  'width'     => '50px',
		  'index'     => 'increment_id',
	  ));
	  
	  $this->addColumn('created_at', array(
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
			'align'     => 'center',
      ));
		
	  /* $this->addColumn('customer_firstname', array(
		  'header'    => __('First Name'),
		  'align'     => 'left',
		  'index'     => 'customer_firstname',
	  ));
	  
	  $this->addColumn('customer_lastname', array(
		  'header'    => __('Last Name'),
		  'align'     => 'left',
		  'index'     => 'customer_lastname',
	  )); */
	  
	  $this->addColumn('customer_email', array(
		  'header'    => __('Email'),
		  'align'     => 'left',
		  'index'     => 'customer_email',
	  ));
	  
	  $this->addColumn('base_grand_total', array(
		  'header'    => __('Base Grand Total'),
		  'align'     => 'center',
		  'type'  	  => 'currency',
		  'index'     => 'base_grand_total',
	  ));
	  
	  $this->addColumn('grand_total', array(
		  'header'    => __('Grand Total'),
		  'align'     => 'center',
		  'type'      => 'currency',
		  'index'     => 'grand_total',
	  ));
	  
	  $this->addColumn(
			'has_design', 
			[
				'header' => __('Has Design'), 
				'index' => 'has_design',
				'align'	=> 'center',
				'width'     => '250px',
				'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
				'sortable'	=> false,
				'type'      => 'options',
				'options'   => array(
					1 => 'Has Design',
					2 => 'No Design',
				),
				'renderer'  => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Hasdesignorder',
				'filter_condition_callback' => array($this, '_filterDesignCondition'),
			]
	  );

	  $this->addColumn(
            'action',
            [
                'header' 	=> __('Action'),
                'type'   	=> 'action',
                'getter' 	=> 'getId',
				'align'	 	=> 'center',
                'actions' 	=> [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => 'onlinedesign/order/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'order_id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
      );

	  return parent::_prepareColumns();
	}


    /**
     * Row click url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['order_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
	
	protected function _filterDesignCondition($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
            return;
		}
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$onlinedesign_pid_arr = array();
		$order_increment_id= array();
		$product = $objectManager->create('Netbaseteam\Onlinedesign\Model\Onlinedesign')->getCollection();
		foreach($product as $p) {
			$onlinedesign_pid_arr[] = $p->getProductId();
		}
		
		$orders = $objectManager->create('Magento\Sales\Model\Order')->getCollection();
		foreach($orders as $o) {
			$order = $objectManager->create('Magento\Sales\Model\Order')->load($o->getId());
			$items = $order->getAllVisibleItems();
			foreach($items as $item):
				if(in_array($item->getProductId(), $onlinedesign_pid_arr)){
					$order_increment_id[] = $order->getIncrementId();
					break;
				}
			endforeach;
		}
		
	   
		if($value == 1) {
			$this->getCollection()
				->addAttributeToFilter('increment_id', array('in' => $order_increment_id))
				->load();
		} else {
			$this->getCollection()
				->addAttributeToFilter('increment_id', array('nin' => $order_increment_id))
				->load();
		}
    }
}
