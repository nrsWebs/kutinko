<?php
/**
 * Options for select valikdation strategy
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Model\Config\System\Source;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Option\ArrayInterface;

class ValidationStrategy implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR, 'label' => __('Stop on Error')],
            ['value' => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS, 'label' => __('Skip error entries')],
        ];
    }
}