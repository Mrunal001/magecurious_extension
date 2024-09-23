<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Plugin\Checkout\Controller\Onepage;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;

class Success
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     **/
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    protected $session;
    protected $customerFactory;

    /**
     * Success constructor.
     *
     * @param \Magento\Framework\Registry             $coreRegistry
     * @param \Magento\Checkout\Model\Session         $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory       $orderFactory
     * @param EncryptorInterface                      $encryptor
     * @param \Magento\Framework\Url\DecoderInterface $decoder
     * @param CustomerSession                         $customerSession
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $decoder,
        CustomerSession $customerSession,
        Session $session,
        CustomerFactory $customerFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->encryptor = $encryptor;
        $this->decoder = $decoder;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param \Magento\Checkout\Controller\Onepage\Success $subject
     */
    public function beforeExecute(\Magento\Checkout\Controller\Onepage\Success $subject)
    {
        $order_Id = $subject->getRequest()->getParam('order_id', false);

        if (!$order_Id) {
            return;
        }

        $orderId = $order_Id;

        if ($orderId && is_numeric($orderId)) {
            $order = $this->_orderFactory->create()->load($orderId);
            if ($order && $order->getId()) {
                $this->_checkoutSession->setLastQuoteId($order->getQuoteId());
                $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                $this->_checkoutSession->setLastOrderId($order->getId());
                $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
                $this->_checkoutSession->setLastOrderStatus($order->getStatus());

                $email = $order->getCustomerEmail();

                if ($email) {
                    $customer = $this->customerFactory->create();
                    $websiteId = $order->getStore()->getWebsiteId();
                    $loadCustomer = $customer->setWebsiteId($websiteId)->loadByEmail($email);
                    $this->session->setCustomerAsLoggedIn($loadCustomer);
                }
            }
        }
    }
}
