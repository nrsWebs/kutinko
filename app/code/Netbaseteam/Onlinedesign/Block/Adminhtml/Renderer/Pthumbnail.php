<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Pthumbnail extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($row["entity_id"]);
		$imageHelper  = $objectManager->get('\Magento\Catalog\Helper\Image');
		$image_url = $imageHelper->init($product, 'product_thumbnail_image')->setImageFile($product->getFile())->resize(75, 75)->getUrl();
		$result = "<img src = '".$image_url."' style='border: 1px solid #ccc;' />";
    	return $result;
	}
}
