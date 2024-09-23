<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */
   
/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Magecurious\CustomerDiscount\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{
    protected $_config;
    protected $_order;
    protected $_source;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magecurious\CustomerDiscount\Helper\Data $helperData,
        array $data = []
    ) {
            $this->_config = $taxConfig;
            $this->helper = $helperData;
            parent::__construct($context, $data);
    }

    public function displayFullSummary()
    {
        return true;
    }

    public function getSource()
    {
        return $this->_source;
    }
    public function getStore()
    {
        return $this->_order->getStore();
    }
    public function getOrder()
    {
        return $this->_order;
    }
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();

        $totals = new \Magento\Framework\DataObject(
            [
            'code' => 'customerdiscount',
            'strong' => false,
            'value' =>$this->getOrder()->getCustomerDiscount(),
            'base_value' =>$this->getOrder()->getCustomerDiscount(),
            'label' => $this->helper->getCustomDiscountTitle(),
            ]
        );
        $parent->addTotal($totals, 'customerdiscount');
        return $this;
    }
}
