<?php

namespace Netbaseteam\Onlinedesign\Block;

/**
 * Onlinedesign content block
 */
class Designlist extends \Magento\Framework\View\Element\Template {
	/**
	 * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
	 */
	protected $_onlinedesignCollectionFactory;

	protected $_productIds;

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
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesignstore\CollectionFactory $onlineDesignStoreCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Helper\Image $helper,
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign $resourceModel,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	) {
		$this->_onlinedesignCollectionFactory = $onlineDesignCollectionFactory;
		$this->_onlinedesignstoreCollectionFactory = $onlineDesignStoreCollectionFactory;
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
	public function getOnlineDesignProductIds() {
		if ($this->_productIds === null) {
			$collection = $this->_onlinedesignstoreCollectionFactory->create()
				->addFieldToFilter('design_image',['neq' => NULL])
				->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
				->addFieldToSelect('product_id')
				->setPageSize(10)
    			->setCurPage(1);
			foreach ($collection as $item) {
				$this->_productIds[] = $item->getProductId();
			}
		}

		return $this->_productIds;
	}

	public function getOnlineDesignProducts() {
		return $this->_productFactory->create()->getCollection()->addIdFilter($this->getOnlineDesignProductIds())->addAttributeToSelect('*');
	}

	/**
	 * @param $productId
	 * @return \Magento\Catalog\Model\Product|\Magento\Catalog\Model\ResourceModel\Product\Collection
	 */
	public function getProductCollection($productId) {
		$collection = $this->_productFactory->create();
		$collection->load($productId);
		return $collection;
	}

	/**
	 * @param $productId
	 * @return string
	 */
	public function getImageDesign($productId) {
		$designImage = $this->getDesignImage($productId, $this->_storeManager->getStore()->getId());
		if (isset($designImage) && $designImage) {
			$src = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$designImage;
			$images = "<img max-width='240' max-height='300' alt='' src = '" . $src . "' />";
			return $images;
		}
	}

	protected function getDesignImage($productId, $storeId) {
		if ($productId) {
			$collection = $this->_onlinedesignstoreCollectionFactory->create()
						->addFieldToFilter('product_id', $productId)
						->addFieldToFilter('store_id', $storeId)
						->addFieldToFilter('design_image',['neq' => NULL])
						->getFirstItem();
			return $collection->getData('design_image');			
		}
	}
}
