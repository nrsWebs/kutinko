<?php

/**
 * Onlinedesign Resource Collection
 */
namespace Netbaseteam\Onlinedesign\Model\ResourceModel\Template;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Netbaseteam\Onlinedesign\Model\Template', 'Netbaseteam\Onlinedesign\Model\ResourceModel\Template');
    }
}
