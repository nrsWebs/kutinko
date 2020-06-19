<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Onlinedesign Resource Model
 */
class Onlinedesign extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	/**
	 * Block store table
	 *
	 * @var string
	 */
	protected $_blockStoreTable;

	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('nb_onlinedesign', 'onlinedesign_id');
		$this->_blockStoreTable = $this->getTable('nb_onlinedesign_store');
	}

	/**
	 * Retrieve store IDs related to given rating
	 *
	 * @param  int $onlineDesignId
	 * @return array
	 */
	public function getStores($onlineDesignId) {
		$select = $this->getConnection()->select()->from(
			$this->getTable($this->_blockStoreTable),
			'store_id'
		)->where(
			'onlinedesign_id = ?',
			$onlineDesignId
		);
		return $this->getConnection()->fetchCol($select);
	}

	/**
	 * @param $productId
	 * @param $storeId
	 * @return array
	 */
	public function getDesignImage($productId, $storeId) {
		if ($productId) {
			$read = "Select design_image FROM " . $this->_blockStoreTable . " Where product_id = " . $productId . " && store_id = " . $storeId;
			$result = $this->getConnection()->fetchAll($read);
			return $result;
		}
	}

	public function checkOnlineDesign() {

	}

	/**
	 * Perform actions before object save
	 *
	 * @param AbstractModel $object
	 * @return $this
	 */
	protected function _beforeSave(AbstractModel $object) {

		if ($object->hasData('stores') && is_array($object->getStores())) {
			$stores = $object->getStores();
			$stores[] = 0;
			$object->setStores($stores);
		} elseif ($object->hasData('stores')) {
			$object->setStores([$object->getStores(), 0]);
		}

		return $this;
	}

	/**
	 * Perform actions after object save
	 *
	 * @param \Magento\Framework\Model\AbstractModel $object
	 * @return $this
	 */
	protected function _afterSave(AbstractModel $object) {
		$connection = $this->getConnection();

		/**
		 * save stores
		 */
		$stores = $object->getStores();
		unset($stores[1]);

		if (!empty($stores)) {

			$productId = $object['product_id'];

			$storeId = $stores[0] ? $stores[0] : 0;
			$read = "SELECT onlinedesign_id FROM {$this->_blockStoreTable} WHERE product_id = {$productId} AND store_id = {$storeId}";
			$result = $connection->fetchAll($read);
			if (!empty($result)) {

				$img = $object['design_image'] ? $object['design_image'] : 1;
				$sql = "Update " . $this->_blockStoreTable . " Set design_image = '" . $img . "'  Where product_id = " . $productId . " && store_id = " .$storeId;
				$connection->query($sql);

			} else {

				$storeId = $stores[0] ? $stores[0] : 0;
				$img = $object['design_image'] ? $object['design_image'] : 1;
				$storeInsert = ['onlinedesign_id' => $object->getId(), 'product_id' => $object['product_id'], 'store_id' => $storeId, 'design_image' => $img];
				$connection->insert($this->_blockStoreTable, $storeInsert);
			}
		}

		return $this;
	}

}
