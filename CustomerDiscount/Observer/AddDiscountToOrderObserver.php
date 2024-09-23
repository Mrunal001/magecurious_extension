<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddDiscountToOrderObserver implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param  EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $CustomFeeFee = $quote->getCustomerDiscount();
        if (!$CustomFeeFee) {
            return $this;
        }
        $order = $observer->getOrder();
        $order->setData('customer_discount', $CustomFeeFee);
        return $this;
    }
}
