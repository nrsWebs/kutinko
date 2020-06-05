<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Imageart extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$result = '';

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
		$list = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'arts.json');
		$art_id = $row['art_id'];
		$art_index_found = $helper->indexFound($art_id, $list, "id");
		if (isset($list[$art_index_found])) {
			$art_data = $list[$art_index_found];
			if(isset($art_data["url"])) {
				$result = '<img src="'.$helper->getMediaPath()."/cliparts/".$art_data["url"].'" width="100" />';
			}
		}
		
    	return $result;
	}
}
