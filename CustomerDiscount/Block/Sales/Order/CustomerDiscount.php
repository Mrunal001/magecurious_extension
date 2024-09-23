<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Block\Sales\Order;

use Magento\Sales\Model\Order;

class CustomerDiscount extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;
    
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecurious\CustomerDiscount\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helperData;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $store = $this->getStore();
        
        $customerdiscount = new \Magento\Framework\DataObject(
            [
                'code' => 'customerdiscount',
                'strong' => false,
                'value' =>  $this->_source->getCustomerDiscount(),
                'label' => $this->helper->getCustomDiscountTitle(),
            ]
        );
        $parent->addTotal($customerdiscount, 'customerdiscount');
       
        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
