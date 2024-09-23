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
use Magento\Checkout\Model\Session;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;

class OrderPlaceAfterObserver implements ObserverInterface
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $quoteFactory;

    public function __construct(
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $this->checkoutSession->getQuoteId();
        if ($quoteId) {
            $quote = $this->quoteFactory->create()->load($quoteId);
            $customerDiscount = $quote->getData('customer_discount');
            if ($customerDiscount !== null) {
                $order->setData('customer_discount', $customerDiscount);
                $this->orderRepository->save($order);
            }
        }
    }
}
