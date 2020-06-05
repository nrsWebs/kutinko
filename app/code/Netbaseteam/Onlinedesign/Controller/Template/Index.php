<?php
namespace Netbaseteam\Onlinedesign\Controller\Template;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_helper;
    public function __construct(
        \Netbaseteam\Onlinedesign\Helper\Data $helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory)
    {
        $this->_helper = $helper;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.nbdesigner.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class.helper.php';
        require_once $this->_helper->getLibOnlineDesign() . '/Onlinedesign/includes/class-util.php';
        return $this->_pageFactory->create();
    }
}