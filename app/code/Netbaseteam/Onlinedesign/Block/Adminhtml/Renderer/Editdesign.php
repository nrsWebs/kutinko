<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Editdesign extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get("\Netbaseteam\Onlinedesign\Helper\Data");
        $frontendUrl = $objectManager->get('\Magento\Framework\UrlInterface');

        $pid = $this->getRequest()->getParam('id');
        $product = $objectManager->create("Magento\Catalog\Model\Product")->load($pid);
        $priority = "primary";
        if($row['folder'] != "primary"){
            $priority = "extra";
        }

        $primary_url = $this->getUrl('*/*/primary').";".$pid.";".$row['folder'];
        $delete_url = $this->getUrl('*/*/deletetemplate').";".$pid.";".$row['folder'];
        // $link_admindesign = $product->getProductUrl()."?product_id=".$pid.'&nbd_item_key='.$row['folder'].'&rd=admin_templates&design_type=template&task=edit';
        $link_admindesign = $frontendUrl->getBaseUrl().'onlinedesign/index/design';
        // $link_admindesign .= "/id/". $pid . '/?design_type=template&nbd_item_key='.$row['folder'].'&rd=admin_templates&task=edit';
        $link_admindesign .= "/id/". $pid . '/?product_id='.$pid.'&nbd_item_key='.$row['folder'].'&rd=admin_templates&design_type=template&task=edit';

        $result = '<a href="'.$link_admindesign.'" target="_blank">Edit</a><br />';
        if($row['folder'] != "primary"){
            // $result .= '<a onclick="primaryBtn(this); return false;" href="javascript:void(0)" target="_blank" data-rev="'.$primary_url.'">Primary</a><br />';
            $result .= '<a onclick="delete_template(this); return false;" href="javascript:void(0)" target="_blank" data-rev="'.$delete_url.'">Delete</a><br />';
        }
        return $result;
    }
}
