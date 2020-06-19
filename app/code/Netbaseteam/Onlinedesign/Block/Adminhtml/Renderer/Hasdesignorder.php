<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer; 

class Hasdesignorder extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		/* $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
		$result = '<span style="font-weight: bold; color: #CDCDCD;text-transform: uppercase;">No Design</span>';
		$status = $helper->getStatusDesign($row['entity_id']);
		if($status == 1) {
			$result = '<span style="font-weight: bold; text-transform: uppercase;">Has Design</span>';
		}
		
    	return $result; */
		
		$onlinedesign_pid_arr = array();
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Netbaseteam\Onlinedesign\Model\Onlinedesign')->getCollection();
		foreach($product as $p) {
			$onlinedesign_pid_arr[] = $p->getProductId();
		}
		
		$status = 0;
		$order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($row["increment_id"]);
	    $items = $order->getAllVisibleItems();
	    foreach($items as $item):
			if($item->getNbdesignerJson() != null || $item->getNbdesignerJson() != "" && in_array($item->getNbdesignerPid(), $onlinedesign_pid_arr)){
				$path = $item->getNbdesignerJson();
				$path_arr = json_decode($path);
				if(file_exists($path_arr[0])){
					$status = 1;
					break;
				}
			}
	    endforeach;
		
    	$result = '<span style="font-weight: bold; color: #CDCDCD;text-transform: uppercase;">No Design</span>';
		if($status) {
			$result = '<span style="font-weight: bold; text-transform: uppercase;">Has Design</span>';
		}
		
    	return $result;
	}
}
