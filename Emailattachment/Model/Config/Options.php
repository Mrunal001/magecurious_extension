<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Options implements ArrayInterface
{
    public function toOptionArray()
    {

        $options = [];
        $options[] = [
            'value' => 'order',
            'label' => 'Order',
        ];
        $options[] = [
            'value' => 'invoice',
            'label' => 'Invoice',
        ];
        $options[] = [
            'value' => 'shipment',
            'label' => 'Shipment',
        ];
        $options[] = [
            'value' => 'creditmemo',
            'label' => 'Credit Memo',
        ];

        return $options;
    }
}
