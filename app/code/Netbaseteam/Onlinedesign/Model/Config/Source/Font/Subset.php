<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source\Font;

class Subset implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => __('All language')],
            ['value' => 'arabic', 'label' => __('Arabic')],
            ['value' => 'bengali', 'label' => __('Bengali')],
            ['value' => 'cyrillic', 'label' => __('Cyrillic')],
            ['value' => 'cyrillic-ext', 'label' => __('Cyrillic Extended')],
            ['value' => 'chinese-simplified', 'label' => __('Chinese (Simplified)')],
            ['value' => 'devanagari', 'label' => __('Devanagari')],
            ['value' => 'greek', 'label' => __('Greek')],
            ['value' => 'greek-ext', 'label' => __('Greek Extended')],
            ['value' => 'gujarati', 'label' => __('Gujarati')],
            ['value' => 'gurmukhi', 'label' => __('Gurmukhi')],
            ['value' => 'hebrew', 'label' => __('Hebrew')],
            ['value' => 'japanese', 'label' => __('Japanese')],
            ['value' => 'kannada', 'label' => __('Kannada')],
            ['value' => 'khmer', 'label' => __('Khmer')],
            ['value' => 'korean', 'label' => __('Korean')],
            ['value' => 'latin', 'label' => __('Latin')],
            ['value' => 'latin-ext', 'label' => __('Latin Extended')],
            ['value' => 'malayalam', 'label' => __('Malayalam')],
            ['value' => 'myanmar', 'label' => __('Myanmar')],
            ['value' => 'oriya', 'label' => __('Oriya')],
            ['value' => 'sinhala', 'label' => __('Sinhala')],
            ['value' => 'tamil', 'label' => __('Tamil')],
            ['value' => 'telugu', 'label' => __('Telugu')],
            ['value' => 'thai', 'label' => __('Thai')],
            ['value' => 'vietnamese', 'label' => __('Vietnamese')]
        ];
    }
}

