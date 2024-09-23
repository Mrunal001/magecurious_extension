<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Model\Config\Source;

class Customergroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'customergroups', 'label' => __('Customer Groups')],
            ['value' => 'specificcustomers', 'label' => __('Specific Customers')],
        ];
    }
}
