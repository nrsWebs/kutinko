<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Netbaseteam\Onlinedesign\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckConditionObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Netbaseteam\Onlinedesign\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Netbaseteam\Onlinedesign\Block\Catalog\Product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * CheckConditionObserver constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Netbaseteam\Onlinedesign\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_request = $request;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $moduleName = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action     = $this->_request->getActionName();
        $route      = $this->_request->getRouteName();
        if ($moduleName == 'onlinedesign' && $route == 'onlinedesign') {
            if (!$this->_helper->releaseLimit()) {
                $this->_messageManager->addWarning(__('Please active the license key to use extension!'));
                $customerBeforeAuthUrl = $this->_url->getUrl();
                $observer->getControllerAction()->getResponse()->setRedirect($customerBeforeAuthUrl);
            }
        }
    }
}
