<?php

namespace Netbaseteam\Onlinedesign\Block;

/**
 * Onlinedesign content block
 */
class Mydesign extends \Magento\Framework\View\Element\Template {
	/**
	 * Onlinedesign collection
	 *
	 * @var \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
	 */
	protected $_orderCollection = null;

	/**
	 * Onlinedesign factory
	 *
	 * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
	 */
	protected $_orderCollectionFactory;

	/** @var \Netbaseteam\Onlinedesign\Helper\Data */
	protected $_dataHelper;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $customerSession;

	/**
	 * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
	 */
	protected $_onlineDesignCollectionFactory;

	/**
	 * @var \Magento\Sales\Model\Order
	 */
	protected $order;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $_onlineDesignCollectionFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\CollectionFactory $_onlineDesignCollectionFactory,
		\Magento\Sales\Model\OrderFactory $orderCollectionFactory,
		\Netbaseteam\Onlinedesign\Helper\Data $dataHelper,
		\Magento\Customer\Model\SessionFactory $customerSession,
		\Magento\Sales\Model\Order $order,
		array $data = []
	) {
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_onlineDesignCollectionFactory = $_onlineDesignCollectionFactory;
		$this->_dataHelper = $dataHelper;
		$this->customerSession = $customerSession;
		$this->order = $order;
		parent::__construct(
			$context,
			$data
		);
	}

	/**
	 * @return get customer id
	 */
	protected function getCustomerId() {
		$customer = $this->customerSession->create();
		return $customer->getCustomer()->getId();
	}

	/**
	 * Retrieve onlinedesign collection
	 *
	 * @return \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
	 */
	protected function _getCollection() {
		$collection = $this->_orderCollectionFactory->create()->getCollection();
		return $collection;
	}

	/**
	 * order collection
	 */
	public function getCollection() {
		$this->_orderCollection = $this->_getCollection()->addAttributeToFilter('customer_id', $this->getCustomerId());
		$this->_orderCollection->setOrder('created_at', 'desc');
		return $this->_orderCollection;
	}

	/**
	 * get collection
	 * @return \Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesign\Collection
	 */
	public function getProductOnlineDesignId($productId) {
		$collection = $this->_onlineDesignCollectionFactory->create()->addFieldToFilter('product_id', $productId)
			->addFieldToFilter('status', 1);
		return $collection;
	}

	/**
	 * @param $orderId
	 * @return string
	 */
	public function getOrderItems($orderId) {
		$order = $this->order->load($orderId);

		$order->getAllVisibleItems();
		$orderItems = $order->getItemsCollection()->addAttributeToSelect('*')->load();
		return $orderItems;
	}
}
