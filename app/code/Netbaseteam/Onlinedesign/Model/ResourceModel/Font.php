<?php

namespace Netbaseteam\Onlinedesign\Model\ResourceModel;

/**
 * Catart Resource Model
 */
class Font extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nb_font', 'font_id');
    }
}
