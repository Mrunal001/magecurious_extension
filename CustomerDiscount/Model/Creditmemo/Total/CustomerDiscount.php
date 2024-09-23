<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
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
     * @param  \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        if ($this->_dataHelper->isEnabled()) {
            $creditmemo->setCustomerDiscount(0);
            $amount = $creditmemo->getOrder()->getCustomerDiscount();
            $creditmemo->setCustomerDiscount($amount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $creditmemo->getCustomerDiscount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $creditmemo->getCustomerDiscount());

            return $this;
        }
    }
}
