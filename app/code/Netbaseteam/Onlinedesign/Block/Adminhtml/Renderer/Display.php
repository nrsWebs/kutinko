<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

class Display extends \Magento\Config\Block\System\Config\Form\Field {

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
		$this->setTemplate('Netbaseteam_Onlinedesign::jscolor.phtml')->toHtml();
    }
}