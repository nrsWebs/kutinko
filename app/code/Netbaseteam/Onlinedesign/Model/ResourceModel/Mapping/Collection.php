<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel\Mapping;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'template_mapping_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Netbaseteam\Onlinedesign\Model\Mapping', 'Netbaseteam\Onlinedesign\Model\ResourceModel\Mapping');
    }
}
