<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Font;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Font\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

/**
 * @var CollectionFactory
 */
	protected $collectionFactory;

	protected $_font;

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
		\Netbaseteam\Onlinedesign\Model\Font $font
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_font = $font;
		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {
		$fontIds = $this->getRequest()->getParam('fontid');
		if (!is_array($fontIds) || empty($fontIds)) {
			$this->messageManager->addError(__('Please select font(s).'));
		} else {
			try {
				foreach ($fontIds as $fontId) {
					$fontc = $this->_font->load($fontId);
					$fontc->delete();
					$this->nbdesigner_delete_font($fontId);
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', count($fontIds))
				);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function nbdesigner_delete_font($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'fonts.json';
		$list = $helper->nbdesigner_read_json_setting($path);
		try {
			$id_found = $helper->indexFound($id, $list, "id");
			$file_font = $helper->plugin_path_data() . $list[$id_found]["file"];
			unlink($file_font);
		} catch (\Exception $e) {}
		$helper->nbdesigner_delete_json_setting($path, $id);
	}
}