<?php

namespace Netbaseteam\Onlinedesign\Model;

/**
 * Onlinedesign Model
 *
 * @method \Netbaseteam\Onlinedesign\Model\Resource\Page _getResource()
 */
class Onlinedesignstore extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Netbaseteam\Onlinedesign\Model\ResourceModel\Onlinedesignstore');
    }

}
