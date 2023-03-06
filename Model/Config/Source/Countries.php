<?php

namespace Esto\HirePurchase\Model\Config\Source;

class Countries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => 'Estonia'],
            ['value' => 1, 'label' => 'Latvia'],
            ['value' => 2, 'label' => 'Lithuania'],
            ['value' => 3, 'label' => 'Finland']
        ];
    }
}
