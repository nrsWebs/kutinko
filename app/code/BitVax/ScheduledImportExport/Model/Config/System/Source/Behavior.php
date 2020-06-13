<?php
/**
 * Options for select behavior
 * 
 * @category BitVax_ScheduledImportExport
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\ScheduledImportExport\Model\Config\System\Source;

use Magento\ImportExport\Model\Source\Import\Behavior\Basic;
use Magento\Framework\Option\ArrayInterface;

class Behavior extends Basic implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toArray();
        $behaviorOptions = array();
        foreach ($options as $key => $option)
        {
            $behaviorOptions[] = array(
                'value' => $key,
                'label' => $option
            );
        }
        return $behaviorOptions;
    }
}