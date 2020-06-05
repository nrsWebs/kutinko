<?php

namespace Netbaseteam\Onlinedesign\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Netbaseteam\Onlinedesign\Controller\AbstractController\OnlinedesignLoaderInterface
     */
    protected $onlinedesignLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, OnlinedesignLoaderInterface $onlinedesignLoader, PageFactory $resultPageFactory)
    {
        $this->onlinedesignLoader = $onlinedesignLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Onlinedesign view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->onlinedesignLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
