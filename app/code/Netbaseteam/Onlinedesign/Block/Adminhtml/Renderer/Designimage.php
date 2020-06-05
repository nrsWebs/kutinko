<?php
namespace Netbaseteam\Onlinedesign\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Designimage extends AbstractRenderer
{
    /**
     * @param \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlineDesignCollectionFactory
     */
    public function __construct(
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlineDesignCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_onlinedesignCollectionFactory = $onlineDesignCollectionFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve onlinedesign collection
     *
     * @return \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
     */
    public function getCollectionOnlineDesign()
    {
        $collection = $this->_onlinedesignCollectionFactory->create();
        return $collection;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if(isset($this->getCollectionOnlineDesign()->addFieldToFilter('product_id',$row['entity_id'])->getData()[0]['design_image']) && $designImage = $this->getCollectionOnlineDesign()->addFieldToFilter('product_id',$row['entity_id'])->getData()[0]['design_image']) {
            $image = ($designImage && @getimagesize($designImage)) ? "<img src = '".$this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$designImage."' style='width: 75px; height: 75px; border: 1px solid #ccc;' />" : '';
            return $image;
        }
    }
}
