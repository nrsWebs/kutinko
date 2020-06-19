<?php

namespace Netbaseteam\Onlinedesign\Plugin\Order\Item;

class Renderer
{
    public function beforeToHtml(\Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $subject)
    {
        $subject->setTemplate('Netbaseteam_Onlinedesign::order/items/renderer/default.phtml');

    }
}