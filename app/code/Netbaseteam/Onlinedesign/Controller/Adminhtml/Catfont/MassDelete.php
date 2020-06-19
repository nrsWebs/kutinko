<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Catfont;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Catfont\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

/**
 * @var CollectionFactory
 */
	protected $collectionFactory;

	protected $_catfont;

	protected $_helper;

/**
 * @param Context $context
 * @param Filter $filter
 * @param CollectionFactory $collectionFactory
 */
	public function __construct(
		Context $context,
		Filter $filter,
		CollectionFactory $collectionFactory,
		\Netbaseteam\Onlinedesign\Helper\Data $helper,
		\Netbaseteam\Onlinedesign\Model\Catfont $catfont
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_catfont = $catfont;
		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {
		$catfontIds = $this->getRequest()->getParam('catfont_id');
		if (!is_array($catfontIds) || empty($catfontIds)) {
			$this->messageManager->addError(__('Please select category font(s).'));
		} else {
			try {
				foreach ($catfontIds as $catId) {
					$cat = $this->_catfont->load($catId);
					$cat->delete();
					$this->nbdesigner_delete_font_cat($catId);
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', count($catfontIds))
				);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function nbdesigner_delete_font_cat($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'font_cat.json';
		$helper->nbdesigner_delete_json_setting($path, $id, true);
		$font_path = $helper->plugin_path_data() . 'fonts.json';
		$helper->nbdesigner_update_json_setting_depend($font_path, $id);
	}
}