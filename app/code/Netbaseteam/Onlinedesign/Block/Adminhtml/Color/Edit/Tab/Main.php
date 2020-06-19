<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Color\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $_dataOnlinedesign;

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
		\Netbaseteam\Onlinedesign\Helper\Data $dataOnlinedesign,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_dataOnlinedesign = $dataOnlinedesign;
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
        $model = $this->_coreRegistry->registry('color');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::onlinedesign_color')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('color_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('')]);

        if ($model->getId()) {
            $fieldset->addField('color_id', 'hidden', ['name' => 'color_id']);
        }

		$helper = $this->_dataOnlinedesign;
		
		$objectManager 	= \Magento\Framework\App\ObjectManager::getInstance();
		$color_load 	= $objectManager->create('Netbaseteam\Onlinedesign\Model\Color')->load($model->getId());
		$hex = $color_load->getHex();
	
		$fieldset->addField('hex', 'text', array(
			'label'     => __('Hex'),
			'class'     => '',
			'required'  => false,
			'name'      => 'hex',
			'style'	  	=> 'width: 85px;'
		));
		
		$renderer = $this->getLayout()->createBlock('Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer\Color');
		$fieldset->setRenderer($renderer);
	  
		$fieldset->addField('color_name', 'text', array(
			'label'     => __('Color Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'color_name',
		));
		
		//$fieldset2 = $form->addFieldset('morecolor_fieldset', ['legend' => __('Add more colors')]);
		//$renderer2 = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')
						 /* ->setTemplate('Netbaseteam_Onlinedesign::jscolor.phtml')
						 ->toHtml() */;
		//$fieldset2->setRenderer($renderer2);
	
        $this->_eventManager->dispatch('adminhtml_color_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
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
        return __('Color Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Color Information');
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
