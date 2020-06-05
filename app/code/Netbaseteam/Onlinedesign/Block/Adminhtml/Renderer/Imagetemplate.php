<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Imagetemplate extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
		
		$result = '';
		$pid = $this->getRequest()->getParam('id');
    	$path_url = $helper->getMediaPath().'/designs/'.$row['folder'].'/preview/';
    	$path = $helper->plugin_path_data().'designs/'.$row['folder'].'/'.'preview'.'/';
        if (file_exists($path) && $handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				$files[] = $entry;
			}
			$images=preg_grep('/\.png$/i', $files);
			foreach($images as $image){
				echo '<img width="100px" style="margin-right: 15px; border: 1px solid #ccc" src="'.$path_url.'/'.$image.'" border="0" />';
			}
			closedir($handle);
		}
		
    	return $result;
	}
}
