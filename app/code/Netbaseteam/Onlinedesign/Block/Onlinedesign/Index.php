<?php

namespace Netbaseteam\Onlinedesign\Block\Onlinedesign;


class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
     */
    protected $_onlinedesignCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlineDesignCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Image $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlineDesignCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $helper,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign $resourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_onlinedesignCollectionFactory = $onlineDesignCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_helper = $helper;
        $this->_resourceModel = $resourceModel;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Retrieve onlinedesign collection
     *
     * @return \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
     */
    public function getCollectionOnlineDesign()
    {
        $collection = $this->_onlinedesignCollectionFactory->create()
            ->addFieldToFilter('status', 1)->setPageSize(8)->setCurPage(1)
            ->addFieldToFilter('show_on_online_design_page', 1)->load();

        return $collection;
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Model\Product|\Magento\Catalog \Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($productId)
    {
        $collection = $this->_productFactory->create();
        $collection->load($productId);
        return $collection;
    }

    /**
     * @param $productId
     * @return string
     */
    public function getImageDesign($productId)
    {
        $designImage = $this->_resourceModel->getDesignImage($productId, 1);
        if (!empty($designImage) && isset($designImage[0]['design_image'])) {
            $product = $this->getProductCollection($productId);
            $productImageUrl = $this->_helper->init($product, 'product_page_image_large')->setImageFile($product->getFile())->resize(310, 294)->getUrl();

            $img = isset($designImage[0]['design_image']) ? $designImage[0]['design_image'] : '';
            $result = ($img && @getimagesize($img)) ? $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$img : $productImageUrl;
            $images = "<img alt='' src = '$result' />";
            return $images;
        }
    }

    /**
     * @param $productId
     * @return string
     */
    public function getProductDesignName($productId)
    {
        $productName = $this->getProductCollection($productId)->getName();
        return $productName;
    }

    /**
     * @param $productId
     * @return string
     */
    public function getProductImage($productId)
    {
        $productImg = $this->getProductCollection($productId)->getImage()
            ? $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $this->getProductCollection($productId)->getImage() : '';

        $images = "<img alt='' src = '$productImg' />";
        return $images;
    }

    /**
     * @param $productId
     * @return string
     */
    public function getProductDesignUrl($productId)
    {
        $productUrl = $this->getProductCollection($productId)->getProductUrl();
        return $productUrl;
    }
}
