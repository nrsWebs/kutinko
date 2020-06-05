<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Catart;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action {
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * Check the permission to run it
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::catart_manage');
	}

	/**
	 * Onlinedesign List action
	 *
	 * @return void
	 */
	public function execute() {
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu(
			'Netbaseteam_Onlinedesign::catart_manage'
		)->addBreadcrumb(
			__('Category Art'),
			__('Category Art')
		)->addBreadcrumb(
			__('Manage Category Art'),
			__('Manage Category Art')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Category Art'));
		return $resultPage;
	}
}
