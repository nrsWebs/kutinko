<?php

/**
 * Onlinedesign Resource Collection
 */
namespace Netbaseteam\Onlinedesign\Model\ResourceModel\Reject;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Netbaseteam\Onlinedesign\Model\Reject', 'Netbaseteam\Onlinedesign\Model\ResourceModel\Reject');
    }
}
