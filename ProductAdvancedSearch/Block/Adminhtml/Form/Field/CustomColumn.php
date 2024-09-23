<?php

/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class CustomColumn extends Select
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
            ['label' => '1', 'value' => '1'],
            ['label' => '2', 'value' => '2'],
            ['label' => '3', 'value' => '3'],
            ['label' => '4', 'value' => '4'],
            ['label' => '5', 'value' => '5'],
            ['label' => '6', 'value' => '6'],
            ['label' => '7', 'value' => '7'],
            ['label' => '8', 'value' => '8'],
            ['label' => '9', 'value' => '9'],
            ['label' => '10', 'value' => '10'],
       ];
    }
}
