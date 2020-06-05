<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Imagecolor extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$result = '<span style="margin: 5px auto; display: block; height: 23px; width: 50px; background: '.$row->getHex().'; border: 1px solid #CCCCCC;"></span>';
		
    	return $result;
	}
}
