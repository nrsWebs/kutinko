<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source;

/**
 * Used in creating options for getting product type value
 *
 */
class ProductOptionType
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Popup')],
            ['value' => '2', 'label' => __('Product Tab')]
        ];
    }
}