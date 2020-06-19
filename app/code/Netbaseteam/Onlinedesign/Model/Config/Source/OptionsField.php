<?php

namespace Netbaseteam\Onlinedesign\Model\Config\Source;

class OptionsField implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'first_name', 'label' => 'Magento First Name'],
            ['value' => 'last_name', 'label' => 'Magento Last Name'],
            ['value' => 'email', 'label' => 'Magento Email'],
            ['value' => 'billing_website', 'label' => 'Billing - Magento Website'],
            ['value' => 'billing_company', 'label' => 'Billing - Magento Company'],
            ['value' => 'billing_phone', 'label' => 'Billing - Magento Phone'],
            ['value' => 'billing_city', 'label' => 'Billing - Magento City'],
            ['value' => 'billing_states', 'label' => 'Billing - Magento States'],
            ['value' => 'billing-post-code', 'label' => 'Billing -Magento Zip'],
            ['value' => 'billing-country', 'label' => 'Billing -Magento Country'],
            ['value' => 'shipping_website', 'label' => 'Shipping - Magento Website'],
            ['value' => 'shipping_company', 'label' => 'Shipping - Magento Company'],
            ['value' => 'shipping_phone', 'label' => 'Shipping - Magento Phone'],
            ['value' => 'shipping_city', 'label' => 'Shipping - Magento City'],
            ['value' => 'shipping_states', 'label' => 'Shipping - Magento States'],
            ['value' => 'shipping_post_code', 'label' => 'Shipping -Magento Zip'],
            ['value' => 'shipping_country', 'label' => 'Shipping -Magento Country'],
        ];
    }

}