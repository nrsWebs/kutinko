<?php

namespace Netbaseteam\Onlinedesign\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Backend\App\Action
{
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
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Netbaseteam_Onlinedesign::order_design');
    }

    /**
     * Onlinedesign List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Netbaseteam_Onlinedesign::order_design'
        )->addBreadcrumb(
            __('Detail product design'),
            __('Detail product design')
        )->addBreadcrumb(
            __('Manage Onlinedesign'),
            __('Manage Onlinedesign')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Detail product design'));
        return $resultPage;
    }
}
