<?php

namespace Netbaseteam\Onlinedesign\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection;
use \Magento\Framework\Registry;

class Design extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_helper;
    protected $_layout;
    protected $registry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\App\Action\Context $context,
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout,
        PageFactory $resultPageFactory
    ) {
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_layout = $layout;
        parent::__construct($context);
    }

    /**
     * Default Onlinedesign Index page
     *
     * @return void
     */
    public function execute()
    {
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class-qrcode.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.nbdesigner.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.helper.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class-util.php';
        $productId = $this->getRequest()->getParam('product_id');
        $blockDesign = $this->_view->getLayout()->createBlock('Netbaseteam\Onlinedesign\Block\Onlinedesign');
        $statusUploadDesing = (int)$blockDesign->getStatusUploadDesign($productId);
        if ($statusUploadDesing == 0 || $statusUploadDesing == null) {
            $this->_view->loadLayout();
            $this->getResponse()->setBody(
                $this->_layout->createBlock('Netbaseteam\Onlinedesign\Block\Onlinedesign')->setTemplate('Netbaseteam_Onlinedesign::nbdesigner-frontend-template.phtml')->toHtml()
            );
        } elseif ($statusUploadDesing && $this->getRequest()->getParam('noupload')){
            $this->_view->loadLayout();
            $this->getResponse()->setBody(
                $this->_layout->createBlock('Netbaseteam\Onlinedesign\Block\Onlinedesign')->setTemplate('Netbaseteam_Onlinedesign::nbdesigner-frontend-template.phtml')->toHtml()
            );
        } else {
            $this->_view->loadLayout();
            $this->getResponse()->setBody(
                $this->_layout->createBlock('Netbaseteam\Onlinedesign\Block\Onlinedesign')->setTemplate('Netbaseteam_Onlinedesign::sections.phtml')->toHtml()
            );
        }
    }
}
