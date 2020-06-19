<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Catart\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {
	/**
	 * @var \Magento\Store\Model\System\Store
	 */
	protected $_systemStore;

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
		array $data = []
	) {
		$this->_systemStore = $systemStore;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	/**
	 * Prepare form
	 *
	 * @return $this
	 */
	protected function _prepareForm() {
		/* @var $model \Magento\Cms\Model\Page */
		$model = $this->_coreRegistry->registry('catart');

		/*
			         * Checking if user have permissions to save information
		*/
		if ($this->_isAllowedAction('Netbaseteam_Onlinedesign::catart_manage')) {
			$isElementDisabled = false;
		} else {
			$isElementDisabled = true;
		}

		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create();

		$form->setHtmlIdPrefix('catart_main_');

		$fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Category Information')]);

		if ($model->getId()) {
			$fieldset->addField('cat_id', 'hidden', ['name' => 'cat_id']);
		}

		$fieldset->addField(
			'title',
			'text',
			[
				'name' => 'title',
				'label' => __('Title'),
				'title' => __('Title'),
				'required' => true,
				'disabled' => $isElementDisabled,
			]
		);

		// $fieldset->addField(
		// 	'status',
		// 	'select',
		// 	[
		// 		'name' => 'status',
		// 		'label' => __('Status'),
		// 		'title' => __('Status'),
		// 		'options' => ['1' => __('Enable'), '0' => __('Disable')],
		// 		'disabled' => $isElementDisabled,
		// 	]
		// );

		$this->_eventManager->dispatch('adminhtml_catart_edit_tab_main_prepare_form', ['form' => $form]);

		$form->setValues($model->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}

	/**
	 * Prepare label for tab
	 *
	 * @return string
	 */
	public function getTabLabel() {
		return __('Category Information');
	}

	/**
	 * Prepare title for tab
	 *
	 * @return string
	 */
	public function getTabTitle() {
		return __('Category Information');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canShowTab() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHidden() {
		return false;
	}

	/**
	 * Check permission for passed action
	 *
	 * @param string $resourceId
	 * @return bool
	 */
	protected function _isAllowedAction($resourceId) {
		return $this->_authorization->isAllowed($resourceId);
	}
}
