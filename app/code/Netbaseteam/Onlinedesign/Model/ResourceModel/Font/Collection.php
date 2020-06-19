<?php

/**
 * Onlinedesign Resource Collection
 */
namespace Netbaseteam\Onlinedesign\Model\ResourceModel\Font;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Netbaseteam\Onlinedesign\Model\Font', 'Netbaseteam\Onlinedesign\Model\ResourceModel\Font');
    }
}
