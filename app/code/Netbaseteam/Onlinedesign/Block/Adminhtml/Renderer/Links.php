<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Links extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product_id = $row->getId();
        $product = $objectManager->create("Magento\Catalog\Model\Product")->load($product_id);
        $frontendUrl = $objectManager->get('\Magento\Framework\UrlInterface');
        $link_admindesign = $frontendUrl->getBaseUrl().'onlinedesign/index/design';
        $link_admindesign .= "?product_id=" . $product_id;

        $helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
        $status = $helper->getStatusDesign($row['entity_id']);
        $create_template = "";

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('nb_templates');
        $sql = "SELECT * FROM " . $tableName." where product_id = ".$product_id." and folder = 'primary'";
        $ret_temp = $connection->fetchAll($sql);

        if($status) {
            if(sizeof($ret_temp)) {
                $create_template = '
					<a href="'.$link_admindesign.'&task=create&rd=admin_templates'.'" target="_blank">Add Template</a>
					<span> | </span>
				';
            } else {
                $create_template = '
					<a href="'.$link_admindesign.'&task=create&rd=admin_templates'.'" target="_blank">Create Template</a>
					<span> | </span>
				';
            }
        }
        $result = $create_template.'
			<a href="'.$this->getUrl('onlinedesign/index/edit', ['id' => $product_id]).'">Manage Design</a>
		';

        return $result;
    }
}
