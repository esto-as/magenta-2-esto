<?php

namespace Esto\HirePurchase\Model\Config\Source;

class Endpoint implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'EE', 'label' => 'Estonian'],
            ['value' => 'LT', 'label' => 'Lithuanian'],
            ['value' => 'LV', 'label' => 'Latvian'],
        ];
    }
}
