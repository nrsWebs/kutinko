<?php
/**
 * Get Data from admin general configuration
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Model\Config\System;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    const XML_PATH_HELLOWORLD = 'scheduled_import_export/';

    /**
     * Get value
     *
     * @param [type] $field
     * @param [type] $storeId
     * @return void
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Get config
     *
     * @param [type] $code
     * @param [type] $storeId
     * @return void
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_HELLOWORLD .'general/'. $code, $storeId);
    }

}