<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CustomAttributeOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options=[
                                ['label' => __('Adidas'), 'value' => 1],
                                ['label' => __('Puma'), 'value' => 2],
                                ['label' => __('Reebok'), 'value' => 3],
                                ['label' => __('VIP'), 'value' => 4],
                                ['label' => __('Nike'), 'value' => 5],
                                ['label' => __('Louis Philip'), 'value' => 6],
                                ['label' => __('Levis'), 'value' => 7],
                            ];
        }
        return $this->_options;
    }
}
