<?php
/**
 * Handler cron
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Cron;

use BitVax\ScheduledImportExport\Model\ScheduledImport;
use BitVax\ScheduledImportExport\Logger\Logger;

class Import
{
    /**
     * ScheduledImport
     *
     * @var BitVax\ScheduledImportExport\Model\ScheduledImport
     */
    protected $scheduledImport;
    /**
     * Logger
     *
     * @var BitVax\ScheduledImportExport\Logger\Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param ScheduledImport $scheduledImport
     * @param Logger $logger
     */
    public function __construct(
        ScheduledImport $scheduledImport,
        Logger $logger
    ) {
        $this->scheduledImport = $scheduledImport;
        $this->logger = $logger;
    }

    /**
     * Import products
     *
     * @return void
     */
    public function execute()
    {
        $csvs = $this->scheduledImport->getCsvsFiles();
        foreach ($csvs as $csv) {
            $this->logger->info('Start: '. $csv['filename']);
            $validate = $this->scheduledImport->validate($csv['csvSource']);
            if (!$validate) {
                $this->logger->info(__('Sorry, but the data is invalid.'));
                continue; 
            } 
            $this->scheduledImport->import($csv['filename']);
            $this->logger->info('-- Finish --');
        }
    }

    
}