<?php

namespace Netbaseteam\Onlinedesign\Block;

/**
 * Onlinedesign content block
 */
class Onlinedesign extends \Magento\Framework\View\Element\Template
{
    /**
     * Onlinedesign collection
     *
     * @var Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
     */
    protected $_onlinedesignCollection = null;

    /**
     * Onlinedesign factory
     *
     * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
     */
    protected $_onlinedesignCollectionFactory;

    /** @var \Netbaseteam\Onlinedesign\Helper\Data */
    protected $_dataHelper;
    protected $_registry;
    protected $_customerSession;
    protected $_product;
    protected $_productRepository;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlinedesignCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\View\Element\Template\Context $context,
        \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $onlinedesignCollectionFactory,
        \Netbaseteam\Onlinedesign\Helper\Data $dataHelper,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_product = $product;
        $this->_registry = $registry;
        $this->_onlinedesignCollectionFactory = $onlinedesignCollectionFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getProductUrl($productId){
        $product = $this->_productRepository->getById($productId);
        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * Retrieve onlinedesign collection
     *
     * @return \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_onlinedesignCollectionFactory->create();
        return $collection;
    }

    public function getStatusUploadDesign($productId)
    {
        if ($this->getRequest()->getParam('task') == 'create' || $this->getRequest()->getParam('task') == 'edit') {
            return 0;
        }
        $collectionDesign = $this->_onlinedesignCollectionFactory->create()->addFieldToFilter('product_id', $productId);
        foreach ($collectionDesign as $collection) {
            return $collection->getStatusUploadDesign();
        }
    }
    public function getTitleProduct() {

        $productId = $this->getRequest()->getParam('id');
        $productCollection = $this->_product->create()->load($productId);
        return $productCollection->getName();

    }
    public function getStatusOnlineDesign(){
        return $this->_scopeConfig->getValue('onlinedesign/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function get_current_user_id()
    {
        $customer_id = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customer_id = $customerSession->getCustomer()->getId();
        }
        return $customer_id;
    }
}
