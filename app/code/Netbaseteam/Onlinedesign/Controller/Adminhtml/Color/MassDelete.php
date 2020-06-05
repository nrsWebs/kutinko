<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Color;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Color\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action {
	/**
	 * @var Filter
	 */
	protected $filter;

/**
 * @var CollectionFactory
 */
	protected $collectionFactory;

	protected $_color;

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
		\Netbaseteam\Onlinedesign\Model\Color $color
	) {
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_color = $color;
		parent::__construct($context);
	}

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public function execute() {
		$colorIds = $this->getRequest()->getParam('colorid');
		if (!is_array($colorIds) || empty($colorIds)) {
			$this->messageManager->addError(__('Please select color(s).'));
		} else {
			try {
				foreach ($colorIds as $colorId) {
					$colorm = $this->_color->load($colorId);
					$colorm->delete();
					$this->nbdesigner_delete_color($colorId);
				}
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) have been deleted.', count($colorIds))
				);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function nbdesigner_delete_color($id) {
		$helper = $this->_objectManager->get('Netbaseteam\Onlinedesign\Helper\Data');
		$path = $helper->plugin_path_data() . 'colors.json';
		$list = $helper->nbdesigner_read_json_setting($path);
		$helper->nbdesigner_delete_json_setting($path, $id);
	}
}