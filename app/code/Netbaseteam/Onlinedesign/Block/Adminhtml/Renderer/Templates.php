<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;
 
class Templates extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
       \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

       protected $_template = 'Netbaseteam_Onlinedesign::renderer/templates.phtml';
	   
       public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
       {
		   $this->_element = $element;
		   $html = $this->toHtml();
		   return $html;
       }
}