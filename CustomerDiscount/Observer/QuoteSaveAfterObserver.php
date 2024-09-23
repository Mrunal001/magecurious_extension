<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class QuoteSaveAfterObserver implements ObserverInterface
{
    protected $quoteRepository;

    public function __construct(CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $customerDiscount = $quote->getData('customer_discount');

        if ($customerDiscount !== null) {
            $quote->setData('customer_discount', $customerDiscount);
            $this->quoteRepository->save($quote);
        }
    }
}
