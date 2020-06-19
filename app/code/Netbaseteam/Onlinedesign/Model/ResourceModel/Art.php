<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel;

/**
 * Catart Resource Model
 */
class Art extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nb_art', 'art_id');
    }
}
