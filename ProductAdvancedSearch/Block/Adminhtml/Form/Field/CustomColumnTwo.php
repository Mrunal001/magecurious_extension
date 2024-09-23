<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class CustomColumnTwo extends Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }
    public function setInputId($value)
    {
        return $this->setId($value);
    }
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions()
    {
        return [
            ['label' => 'Color', 'value' => 'color'],
            ['label' => 'Description', 'value' => 'description'],
            ['label' => 'SKU', 'value' => 'sku'],
            ['label' => 'Product Name', 'value' => 'name'],
            ['label' => 'Brand', 'value' => 'brand'],
            ['label' => 'Price', 'value' => 'price'],
            ['label' => 'Manufacturer', 'value' => 'manufacturer'],
            ['label' => 'Short Description', 'value' => 'short description'],
            ['label' => 'Sleev', 'value' => 'sleev'],
            ['label' => 'Weight', 'value' => 'weight'],
       ];
    }
}
