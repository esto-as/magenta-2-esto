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
            ['value' => 'https://api.esto.ee/v2/purchase/redirect', 'label' => 'Estonian'],
            ['value' => 'https://api.estopay.lt/v2/purchase/redirect', 'label' => 'Lithuanian'],
            ['value' => 'https://api.esto.lv/v2/purchase/redirect', 'label' => 'Latvian'],
        ];
    }
}
