<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel;

/**
 * Catcolor Resource Model
 */
class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nbdesigner_templates', 'id');
    }
}
