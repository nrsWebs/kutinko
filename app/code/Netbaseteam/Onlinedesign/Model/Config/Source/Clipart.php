<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source;

/**
 * Used in creating options for getting product type value
 *
 */
class Clipart
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'change-color-path', 'label' => __('Change color path')],
            ['value' => 'rotate', 'label' => __('Rotate')],
            ['value' => 'opacity', 'label' => __('Opacity')]
        ];
    }
}