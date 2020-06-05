<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Onlinedesignstore Resource Model
 */
class Onlinedesignstore extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('nb_onlinedesign_store', 'onlinedesign_id');
	}

}
