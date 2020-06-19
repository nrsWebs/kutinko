<?php

namespace Netbaseteam\Onlinedesign\Model;

/**
 * Onlinedesign Model
 *
 * @method \Netbaseteam\Onlinedesign\Model\Resource\Page _getResource()
 * @method \Netbaseteam\Onlinedesign\Model\Resource\Page getResource()
 */
class Catart extends \Magento\Framework\Model\AbstractModel {
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;
	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('Netbaseteam\Onlinedesign\Model\ResourceModel\Catart');
	}

	public static function getAvailableStatuses() {
		return [
			self::STATUS_ENABLED => __('Enabled')
			, self::STATUS_DISABLED => __('Disabled'),
		];
	}

}
