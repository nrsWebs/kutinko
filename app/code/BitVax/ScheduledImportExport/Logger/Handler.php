<?php
/**
 * Extend logger MAgneto 2 to log in custom file
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/scheduled_import.log';
}