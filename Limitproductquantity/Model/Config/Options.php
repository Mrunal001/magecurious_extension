<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Options implements ArrayInterface
{
    public function toOptionArray()
    {

        $options = [];
        $options[] = [
            'value' => '0',
            'label' => 'NOT LOGGED IN',
        ];
        $options[] = [
            'value' => '1',
            'label' => 'General',
        ];
        $options[] = [
            'value' => '2',
            'label' => 'Wholesale',
        ];
        $options[] = [
            'value' => '3',
            'label' => 'Retailer',
        ];

        return $options;
    }
}
