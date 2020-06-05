<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source;

/**
 * Used in creating options for getting product type value
 *
 */
class Font
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'change-font', 'label' => __('Change Font')],
            ['value' => 'italic', 'label' => __('Italic')],
            ['value' => 'bold', 'label' => __('Bold')],
            ['value' => 'underline', 'label' => __('Underline')],
            ['value' => 'line-throught', 'label' => __('Line-through')],
            ['value' => 'overline', 'label' => __('Overline')],
            ['value' => 'text-case', 'label' => __('Text case')],
            ['value' => 'align-left', 'label' => __('Align left')],
            ['value' => 'align-right', 'label' => __('Align Right')],
            ['value' => 'align-center', 'label' => __('Align Center')],
            ['value' => 'text-color', 'label' => __('Text color')],
            ['value' => 'text-bg', 'label' => __('Text Background')],
            ['value' => 'text-shadow', 'label' => __('Text shadow')],
            ['value' => 'line-height', 'label' => __('Line height')],
            ['value' => 'spacing', 'label' => __('Spacing')],
            ['value' => 'font-size', 'label' => __('Font size')],
            ['value' => 'opacity', 'label' => __('Opacity')],
            ['value' => 'outline', 'label' => __('Outline')],
            ['value' => 'unlock-proportion', 'label' => __('Unlock proportion')],
            ['value' => 'rotate', 'label' => __('Rotate')],
        ];
    }
}