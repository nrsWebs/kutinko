<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Googlefont;

/**
 * Adminhtml Onlinedesign grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Netbaseteam\Onlinedesign\Model\Onlinedesign
     */
    protected $_onlinedesign;
	protected $_dataHelper;

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
		\Netbaseteam\Onlinedesign\Helper\Data $_dataHelper,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Font\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_onlinedesign = $onlinedesign;
        $this->_dataHelper = $_dataHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		$this->setTemplate('Netbaseteam_Onlinedesign::googlefont.phtml');
    }
	
	public function getGoogleFontData(){
		$path = $this->_dataHelper->plugin_path_data().'googlefonts.json';
		$list = $this->_dataHelper->nbdesigner_read_json_setting($path);
		return $list;
	}

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection1()
    {
		//$collection = new \Magento\Framework\Data\Collection(); 
		$list = $this->getGoogleFontData();
		$collection = $this->_collectionFactory->create();
		foreach ($list as $row) {
			$rowObj = new \Magento\Framework\DataObject();
			$rowObj->setData($row);
			$collection->addItem($rowObj);
		}           
		
        //$collection = $this->_collectionFactory->create();
        /* @var $collection \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns1()
    {
        $this->addColumn('id', array(
          'header'    => __('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
		  'filter_condition_callback' => array($this, '_filterIDCondition'),
		));

		$this->addColumn('name', array(
          'header'    => __('Title'),
          'align'     =>'left',
          'index'     => 'name',
		  'filter_condition_callback' => array($this, '_filterNameCondition'),
		));
	  
		$this->addColumn('name_preview', array(
          'header'    => __('Preview'),
          'align'     =>'center',
		  'width'     => '250px',
          'index'     => 'name_preview',
		  'sortable'  => false,
		  'filter'	  => false,
		  /* 'renderer'  => 'onlinedesign/adminhtml_renderer_gfont', */
		));

        return parent::_prepareColumns();
    }

	protected function _filterIDCondition($collection, $column)
    {
       if (!$value = $column->getFilter()->getValue()) {
            return;
        }

	  $collection = new Varien_Data_Collection(); 
	  $list = $this->getGoogleFontData();

	  foreach ($list as $row) {
		  if($row['id'] == $value) {
			$rowObj = new Varien_Object();
			$rowObj->setData($row);
			$collection->addItem($rowObj);
		  }
	  }           
	  $this->setCollection($collection);
    }
	
    /**
     * Row click url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['googlefont_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('onlinedesign/googlefont/grid', ['_current' => true]);
    }
}
