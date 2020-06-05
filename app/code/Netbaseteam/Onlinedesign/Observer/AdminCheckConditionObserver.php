<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Netbaseteam\Onlinedesign\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminCheckConditionObserver implements ObserverInterface
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
     * AdminCheckConditionObserver constructor.
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
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\UrlInterface $url,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Config\Block\System\Config\Tabs $tabs
    ) {
        $this->_request = $request;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_tabs = $tabs;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $moduleName = $this->_request->getModuleName();
        $route      = $this->_request->getRouteName();
        if (($this->_request->getFullActionName() == 'adminhtml_system_config_edit' && $this->_request->getParam('section') == 'onlinedesign') || ($moduleName == 'onlinedesign' && $route == 'onlinedesign')) {
            if($this->_request->getFullActionName() == 'onlinedesign_upgrade_index' || $this->_request->getFullActionName() == 'onlinedesign_upgrade_getlicense') return $this;
            if ($this->_helper->releaseLimit() == 'free') {
                $this->_messageManager->addWarning('Please upgrade <a href="https://cmsmart.net/magento-extensions/magento-premium-online-product-designer-extension" target="_blank">Onlinedesign Module Premium</a> version to get more features and supports!');
            } elseif ($this->_helper->releaseLimit() == 'expired') {
                $message = __('Your support, download upgrade has expired Extend now <a href="https://cmsmart.net/your-profile/purchase_download">(Discount 45% off)</a>');
                $this->_messageManager->addWarning($message);
            } elseif(!$this->_helper->releaseLimit()) {
                $this->_messageManager->addWarning(__('Please active the license key to use extension!'));
                $customUrl = $this->_url->getUrl('adminhtml/system_config/edit/section/nb_onlinedesign_license');
                $this->_responseFactory->create()->setRedirect($customUrl)->sendResponse();
                exit();
            }
        }
    }
}
