<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        \Magecurious\CustomerDiscount\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currency = $currency;
        $this->helper = $helperData;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();
        
        $totals = new \Magento\Framework\DataObject(
            [
                'code' => 'customerdiscount',
                'value' => $this->getOrder()->getCustomerDiscount(),
                'label' => $this->helper->getCustomDiscountTitle(),
            ]
        );
        $this->getParentBlock()->addTotalBefore($totals, 'grand_total');

        return $this;
    }
}
