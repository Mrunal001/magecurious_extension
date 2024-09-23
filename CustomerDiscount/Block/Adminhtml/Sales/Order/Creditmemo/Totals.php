<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

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

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();
        $totals = new \Magento\Framework\DataObject(
            [
                'code' => 'customerdiscount',
                'strong' => false,
                'value' => $this->getCreditmemo()->getCustomerDiscount(),
                'label' => $this->helper->getCustomDiscountTitle(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($totals, 'grand_total');

        return $this;
    }
}
