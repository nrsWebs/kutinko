<?php

namespace Netbaseteam\Onlinedesign\Controller\Myaccount;

use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Framework\App\Action\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
	/**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
	
    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(); 
		$this->_view->renderLayout(); 
    }
}
