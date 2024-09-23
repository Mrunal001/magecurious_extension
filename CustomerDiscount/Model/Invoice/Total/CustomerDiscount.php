<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use \Magecurious\CustomerDiscount\Helper\Data;

class CustomerDiscount extends AbstractTotal
{
    protected $_dataHelper;

    public function __construct(
        Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param  \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        if ($this->_dataHelper->isEnabled()) {
            $invoice->setCustomerDiscount(0);
            $amount = $invoice->getOrder()->getCustomerDiscount();
            $invoice->setCustomerDiscount($amount);
            $invoice->setGrandTotal($invoice->getGrandTotal() - $invoice->getCustomerDiscount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $invoice->getCustomerDiscount());

            return $this;
        }
    }
}
