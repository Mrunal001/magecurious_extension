<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Model\Total\Quote;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Magecurious\CustomerDiscount\Helper\Data;

class CustomerDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_dataHelper;
    protected $_priceCurrency;
    protected $_customerRepository;
    protected $quoteRepository;
    protected $discount;
    protected $orderCollectionFactory;

    /**
     * Custom constructor.
     *
     * @param PriceCurrencyInterface      $priceCurrency
     * @param CustomerRepositoryInterface $customerRepository
     * @param CartRepositoryInterface     $quoteRepository
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $quoteRepository,
        CollectionFactory $orderCollectionFactory,
        Data $dataHelper
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param  \Magento\Quote\Model\Quote                          $quote
     * @param  \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param  \Magento\Quote\Model\Quote\Address\Total            $total
     * @return $this|bool
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if ($this->_dataHelper->isEnabled()) {
            $customerId = $quote->getCustomerId();

            if ($customerId !== null || $quote->getCustomerIsGuest()) {
                try {
                    $customer = $this->_customerRepository->getById($customerId);
                    $customerDiscountAttribute = $customer->getCustomAttribute('customer_discount');
                    $discountPercentage = $customerDiscountAttribute ? $customerDiscountAttribute->getValue() : 0;
                } catch (\Exception $e) {
                    $discountPercentage = 0;
                }

                $discount = ($discountPercentage / 100) * $total->getSubtotal();

                $applydiscount = true;

                if (!empty($customerId)) {
                    $orderCollection = $this->orderCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $customerId);

                    if ($orderCollection->getSize() > 0) {
                        foreach ($orderCollection as $orderdata) {
                            if ($orderdata->getCustomerDiscount()) {
                                $applydiscount = false;
                                break;
                            }
                        }
                    } else {
                        $total->addTotalAmount('customdiscount', -$discount);
                        $total->addBaseTotalAmount('customdiscount', -$discount);
                        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $discount);
                    }
                }

                $this->discount = $discount;

                if (!empty($this->discount) && $this->discount > 0 && $applydiscount) {
                    $quote->setCustomerDiscount($this->discount);
                    $quote->setData('customer_discount', $this->discount);
                }
                if ($this->_dataHelper->isAgainEnabled() && $discount > 0) {
                    $quote->setCustomerDiscount($discount);
                    $quote->setData('customer_discount', $discount);

                    if ($orderCollection->getSize() > 0) {
                        $quote->setCustomerDiscount($discount);
                        $quote->setData('customer_discount', $discount);
                        $total->addTotalAmount('customdiscount', -$discount);
                        $total->addBaseTotalAmount('customdiscount', -$discount);
                        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $discount);
                    }
                }
            }
        }

        return $this;
    }

    public function discountValue(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->_dataHelper->isEnabled()) {
            if ($quote) {
                return $quote->getCustomerDiscount();
            }
            return 0;
        }
    }
}
