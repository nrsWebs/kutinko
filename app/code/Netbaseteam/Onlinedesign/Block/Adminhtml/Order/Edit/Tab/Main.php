<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Order\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
	protected $_productloader;  
	protected $_order;  

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Sales\Model\Order $_order,
		\Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
		$this->_productloader = $_productloader;
		$this->_order = $_order; 
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('order');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('order_onlinedesign_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Order Information')]);

        /* if ($model->getId()) {
            $fieldset->addField('product_id', 'hidden', ['name' => 'onlinedesign_id']);
        } */
		
		$_orderId = $this->getRequest()->getParam('order_id');
		/** @var $_order \Magento\Sales\Model\Order  */
		$_order = $this->_order->load($_orderId);
		/* \zend_debug::dump($_order->getData()); */
		//$product = $this->_productloader->create()->load($id);
		
		$str_o = '<strong><a href="' . $this->getUrl('sales/order/view', array('order_id' => $_orderId)) . '" onclick="this.target=\'blank\'">#'. $_order->getIncrementId() . '</a></strong>';
		$str_c = '<strong><a href="' . $this->getUrl('customer/index/edit', array('id' => $_order->getCustomerId())) . '" onclick="this.target=\'blank\'">'. $_order->getCustomerEmail() . '</a></strong>';
		
		$fieldset->addField('pname', 'note', array(
          'label'     => __('Order'),
          'name'      => 'note',
		  'text'     => $str_o,
        )); 

		$fieldset->addField('psku', 'note', array(
          'label'     => __('Customer Email'),
          'name'      => 'note',
		  'text'      => $str_c,
        ));
		
		$fieldset->addField('created_at', 'note', array(
          'label'     => __('Created At'),
          'name'      => 'created_at',
		  'text'      => $_order->getCreatedAt(),
        ));
		
		/* onlinedesign grid template */

        $this->_eventManager->dispatch('adminhtml_onlinedesign_edit_tab_main_prepare_form', ['form' => $form]);

		$fieldset_t = $form->addFieldset('template_fieldset', ['legend' => __('Onlinedesign Templates')]);
		
		$renderer = $this->getLayout()->createBlock(
			   'Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Designorder'
		);
		
		$fieldset_t->setRenderer($renderer);
		
        $form->setValues('' /* $model->getData() */);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
