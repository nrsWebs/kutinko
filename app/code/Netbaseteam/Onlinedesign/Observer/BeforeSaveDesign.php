<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Netbaseteam\Onlinedesign\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeSaveDesign implements ObserverInterface
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
     * @var \Magento\Config\Block\System\Config\Tabs
     */
    protected $_tabs;

    /**
     * BeforeSaveDesign constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Netbaseteam\Onlinedesign\Helper\Data $helper
     * @param \Netbaseteam\Onlinedesign\Block\Catalog\Product $product
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Config\Block\System\Config\Tabs $tabs
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Config\Block\System\Config\Tabs $tabs,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->_request = $request;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_tabs = $tabs;
        $this->_actionFlag = $actionFlag;
        $this->resultRedirectFactory = $redirectFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->releaseLimit() == 'free') {
            $count = $observer->getData('count');
            if ($count > 5) {
                $this->_messageManager->addWarning('Please upgrade Onlinedesign Module Premium version to add more design!');
                $customerBeforeAuthUrl = $this->_url->getUrl('onlinedesign/index/index');

                echo "<script> location.href='<?=$customerBeforeAuthUrl?>'; </script>";
                exit;
            }
        }
    }
}
