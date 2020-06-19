<?php

namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Onlinedesign\Edit\Tab;

class Templatelist extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_odFactory;
    protected $registry;
    protected $_objectManager = null;
    protected $_templateFactoryCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Netbaseteam\Onlinedesign\Model\Onlinedesign $odFactory,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Mapping\CollectionFactory $templateFactoryCollection,
        array $data = []
    ) {
        $this->_templateFactoryCollection = $templateFactoryCollection;
        $this->_objectManager = $objectManager;
        $this->_odFactory = $odFactory;
        $this->registry = $registry;
        
        parent::__construct($context, $backendHelper, $data);
    }

    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('relatedGrid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection()
    {
        $current_id =  $this->getRequest()->getParam('id');
        $collection = $this->_templateFactoryCollection->create();
        $collection->addFieldToFilter('product_id', array('eq'=>$current_id));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'priority',
            [
                'header' => __('Primary'),
                'index' => 'priority',
                'class' => 'xxx',
                'width' => '100px',
            ]
        );

        $this->addColumn(
            'folder',
            [
                'header' => __('Folder'),
                'index' => 'folder',
                'class' => 'xxx',
                'width' => '100px',
            ]
        );

		$this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'class' => 'xxx',
                'width' => '100px',
            ]
        );
        
        $this->addColumn('action', [
			'header'    => __('Action'),
			'align'     =>'center',
			'width'     => '80px',
			'index'     => 'action',
			'sortable'  => false,
			'renderer'  => '\Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Editdesign',
			'filter'	=> false,
			'sortable'      => false,
		]);
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {       
        return $this->getUrl('*/*/templategrid', ['_current' => true]);
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return true;
    }
}
