<?php
/**
 * Magecurious_ProductAdvancedSearch
 * @package   Magecurious\ProductAdvancedSearch
 * @author    magecurious<support@magecurious.com>
 */

namespace Magecurious\ProductAdvancedSearch\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magecurious\ProductAdvancedSearch\Block\Adminhtml\Form\Field\CustomColumn;
use Magecurious\ProductAdvancedSearch\Block\Adminhtml\Form\Field\CustomColumnTwo;

class DynamicFieldData extends AbstractFieldArray 
{
       private $dropdownRenderer;
       private $dropdownRendererTwo;
       protected function _prepareToRender() {
              $this->addColumn(
                     'attribute_name',
              [
                            'label' => __('Attribute'),
                            'renderer' => $this->getDropdownRendererSecond(),
                     ]
              );
              $this->addColumn(
                     'dropdown_field',
                     [
                            'label' => __('Weight'),
                            'renderer' => $this->getDropdownRenderer(),
                     ]
              );
              $this->_addAfter = false;
              $this->_addButtonLabel = __('Add');
       }
       protected function _prepareArrayRow(DataObject $row) {
              $options = [];
              $dropdownField = $row->getDropdownField();
              if ($dropdownField !== null) {
                     $options['option_' . $this->getDropdownRenderer()->calcOptionHash($dropdownField)] = 'selected="selected"';
                     $options['option_' . $this->getDropdownRendererSecond()->calcOptionHash($dropdownField)] = 'selected="selected"';
              }
              $row->setData('option_extra_attrs', $options);
       }
       private function getDropdownRenderer() {
              if (!$this->dropdownRenderer) {
                     $this->dropdownRenderer = $this->getLayout()->createBlock(
                            CustomColumn::class,
                            '',
                            ['data' => ['is_render_to_js_template' => true]]
                     );
              }
              return $this->dropdownRenderer;
       }
       private function getDropdownRendererSecond() {
        if (!$this->dropdownRendererTwo) {
               $this->dropdownRendererTwo = $this->getLayout()->createBlock(
                      CustomColumnTwo::class,
                      '',
                      ['data' => ['is_render_to_js_template' => true]]
               );
        }
              return $this->dropdownRendererTwo;
       }     
}
