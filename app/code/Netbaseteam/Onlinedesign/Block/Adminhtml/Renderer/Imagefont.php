<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Imagefont extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$result = '';
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
				
		$model = $objectManager->create("\Netbaseteam\Onlinedesign\Model\Font")->load($row['font_id']);
    	$alias =  $model->getAlias();

		$list = $helper->nbdesigner_read_json_setting($helper->plugin_path_data().'fonts.json');
		$url = "";
		$font_id = $row['font_id'];
		$font_index_found = $helper->indexFound($font_id, $list, "id");
		if (isset($list[$font_index_found])) {
			$font_data = $list[$font_index_found];
			$url = $helper->getMediaPath()."/".$font_data["url"];
		}
		
    	$result = '
			<style type="text/css">
				@font-face {font-family: '.$alias.';src: local("☺"), url("'.$url.'")}	
			</style>
		';
		$result .= '
			<span style="font-family: '.$alias.', sans-serif;font-size: 30px;">Abc Xyz</span>
		';
		
    	return $result;
	}
}
