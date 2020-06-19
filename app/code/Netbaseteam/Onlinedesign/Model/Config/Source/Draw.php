<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source;

/**
 * Used in creating options for getting product type value
 *
 */
class Draw
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'brush', 'label' => __('Brush')],
            ['value' => 'pencil', 'label' => __('Pencil')],
            ['value' => 'circle', 'label' => __('Circle')],
            ['value' => 'spray', 'label' => __('Spray')],
            ['value' => 'pattern', 'label' => __('Pattern')],
            ['value' => 'hline', 'label' => __('Hline')],
            ['value' => 'vline', 'label' => __('Vline')],
            ['value' => 'square', 'label' => __('Square')],
            ['value' => 'diamond', 'label' => __('Diamond')],
            ['value' => 'texture', 'label' => __('Texture')],
            ['value' => 'geometrical-shape', 'label' => __('Geometrical Shape')],
            ['value' => 'rectangle', 'label' => __('Rectangle')],
            ['value' => 'circle', 'label' => __('Circle')],
            ['value' => 'triangle', 'label' => __('Triangle')],
            ['value' => 'line', 'label' => __('Line')],
            ['value' => 'polygon', 'label' => __('Polygon')],
            ['value' => 'hexagon', 'label' => __('Hexagon')]
        ];
    }
}