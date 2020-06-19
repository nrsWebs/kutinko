<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source\Icc;

/**
 * Used in creating options for getting product type value
 *
 */
class Profile
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Don\'t Color Manage')],
            ['value' => '1', 'label' => __('Coated FOGRA27')],
            ['value' => '2', 'label' => __('Coated FOGRA39')],
            ['value' => '3', 'label' => __('Coated GRACoL 2006')],
            ['value' => '4', 'label' => __('Japan Color 2001 Coated')],
            ['value' => '5', 'label' => __('Japan Color 2001 Uncoated')],
            ['value' => '6', 'label' => __('Japan Color 2002 Newspaper')],
            ['value' => '7', 'label' => __('Japan Color 2003 Web Coated')],
            ['value' => '8', 'label' => __('Japan Web Coated')],
            ['value' => '9', 'label' => __('Uncoated FOGRA29')],
            ['value' => '10', 'label' => __('US Web Coated SWOP')],
            ['value' => '11', 'label' => __('US Web Uncoated')],
            ['value' => '12', 'label' => __('Web Coated FOGRA28')],
            ['value' => '13', 'label' => __('Web Coated SWOP 2006 Grade 3')],
            ['value' => '14', 'label' => __('Web Coated SWOP 2006 Grade 5')],
        ];
    }
}