<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Art;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Art\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

/**
 * @var CollectionFactory
 */
	protected $collectionFactory;

	protected $_art;

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
		\Netbaseteam\Onlinedesign\Model\Art $art
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_art = $art;
		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {
		$artIds = $this->getRequest()->getParam('artid');
		if (!is_array($artIds) || empty($artIds)) {
			$this->messageManager->addError(__('Please select art(s).'));
		} else {
			try {
				foreach ($artIds as $artId) {
					$art = $this->_art->load($artId);
					$art->delete();
					$this->nbdesigner_delete_art($artId);
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', count($artIds))
				);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function nbdesigner_delete_art($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'arts.json';
		$list = $helper->nbdesigner_read_json_setting($path);
		$id_found = $helper->indexFound($id, $list, "id");
		$file_art = $helper->plugin_path_data() . $list[$id_found]["file"];
		try {
			unlink($file_art);
		} catch (\Exception $e) {}
		$helper->nbdesigner_delete_json_setting($path, $id);
	}
}