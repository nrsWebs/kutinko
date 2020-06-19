<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Hasdesign extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
		$result = '<span style="font-weight: bold; color: #CDCDCD;text-transform: uppercase;">No Design</span>';
		$status = $helper->getStatusDesign($row['entity_id']);
		if($status == 1) {
			$result = '<span style="font-weight: bold; text-transform: uppercase;">Has Design</span>';
		}
		
    	return $result;
	}
}
