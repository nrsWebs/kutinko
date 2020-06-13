<?php
/**
 * Import product
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Model;

use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use BitVax\ScheduledImportExport\Logger\Logger;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use BitVax\ScheduledImportExport\Model\Config\System\Data as ConfigImport;

class ScheduledImport
{

    CONST DEFAULT_PATH = "var/scheduled_import";
    /**
     * ImportFactory
     *
     * @var Magento\ImportExport\Model\ImportFactory
     */
    protected $import;
    /**
     * ImportFactory
     * 
     * @var Magento\ImportExport\Model\ImportFactory
     */
    protected $importFactory;
    /**
     * CsvFactory
     * 
     * @var Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    protected $csvSourceFactory;
    /**
     * ReadFactory
     * 
     * @var Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;
    /**
     * DirectoryList
     *
     * @var Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * File
     *
     * @var Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * Logger
     *
     * @var BitVax\ScheduledImportExport\Logger\Logger
     */
    protected $logger;
    /**
     * Data
     *
     * @var BitVax\ScheduledImportExport\Model\Config\System\Data
     */
    protected $configImport;

    /**
     * Consgtructor
     *
     * @param ImportFactory $importFactory
     * @param CsvFactory $csvSourceFactory
     * @param ReadFactory $readFactory
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Logger $logger
     * @param ConfigImport $configImport
     */
    public function __construct(
        ImportFactory $importFactory,
        CsvFactory $csvSourceFactory,
        ReadFactory $readFactory,
        DirectoryList $directoryList,
        File $file,
        Logger $logger,
        ConfigImport $configImport
    ) {
        $this->importFactory = $importFactory;
        $this->csvSourceFactory = $csvSourceFactory;
        $this->readFactory = $readFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->logger = $logger;
        $this->configImport = $configImport;

        $this->import = $this->importFactory->create();
        $this->_setData();
    }

    /**
     * Validate csv
     *
     * @param [type] $sourceCsv
     * @return void
     */
    public function validate($sourceCsv)
    {
        $validate = $this->import->validateSource($sourceCsv);
        if(!$validate) {
            $errorAggregator = $this->import->getErrorAggregator();
            $rowMessages = $errorAggregator->getRowsGroupedByErrorCode([], [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
            foreach ($rowMessages as $errorCode => $rows) {
                $message = $errorCode . ' ' . __('in row(s):') . ' ' . implode(', ', $rows);
                $this->logger->info($message);
            }
            
        } 
        return $validate;
    }

    /**
     * Import csv
     *
     * @param [type] $filename
     * @return void
     */
    public function import($filename)
    {
        $result = $this->import->importSource();
        if ($result) {
            $this->import->invalidateIndex();
            $basePathCsvToImport = $this->getFullPath();
            $file = $basePathCsvToImport.'/'.$filename;
            if ($this->file->isExists($file)) {
                $this->file->deleteFile($file);
            }
        } else {
            $this->logger->info(__('Sorry, but the import process has failed'));
        }
    }

    /**
     * Get csv files
     *
     * @return void
     */
    public function getCsvsFiles()
    {
        $basePathCsvToImport = $this->getFullPath();
        $csvs = array();
        if (is_dir($basePathCsvToImport)) {
            $directoryCsv = $this->file->readDirectory($basePathCsvToImport);
            if (is_array($directoryCsv)) {
                foreach ($directoryCsv as $csv) {
                    $csvInfo = pathinfo($csv);
                    if (array_key_exists('extension', $csvInfo) && $csvInfo['extension'] == 'csv') {
                        $csvs[] = array(
                            'filename' => $csvInfo['basename'],
                            'csvSource' => $this->csvSourceFactory->create(
                                array(
                                    'file' => $csvInfo['basename'],
                                    'directory' => $this->readFactory->create($csvInfo['dirname'])
                                )
                            )
                        );
                        
                    }
                }
            }
        } else {
            $this->logger->info(__("Directory $basePathCsvToImport not exists"));
        }
        
        return $csvs;
    }

    /**
     * Set import configuration
     *
     * @return void
     */
    private function _setData()
    {
        $this->import->setData(
            array(
                'entity' => 'catalog_product',
                'behavior' => $this->configImport->getGeneralConfig('behavior'),
                'validation_strategy' => $this->configImport->getGeneralConfig('validation_strategy'),
                'allowed_error_count' => $this->configImport->getGeneralConfig('allowed_error_count'),
                '_import_field_separator' => $this->configImport->getGeneralConfig('import_field_separator'),
                '_import_multiple_value_separator' => $this->configImport->getGeneralConfig('import_multiple_value_separator'),
                '_import_empty_attribute_value_constant' => $this->configImport->getGeneralConfig('import_empty_attribute_value_constant')
            )
        );
    }

    /**
     * Get full path where csv are saved
     *
     * @return void
     */
    private function getFullPath()
    {
        $rootPath = $this->directoryList->getRoot();
        return $rootPath . '/' . self::DEFAULT_PATH;
    }
}