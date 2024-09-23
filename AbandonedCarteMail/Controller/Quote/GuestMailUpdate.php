<?php

/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\AbandonedCarteMail\Controller\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;

class GuestMailUpdate extends \Magento\Framework\App\Action\Action
{
    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        CheckoutSession $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
                $quoteId = $this->getQuotes()->getId();
                $post = $this->getRequest()->getPostValue();
                $emailId = $post['email'];
                $quote = $this->quoteRepository->get($quoteId);
                $quote->setData('customer_email', $emailId);
                $this->quoteRepository->save($quote);
                $message = __('Thank you for Subscribing');
                $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
                $message = __('Please Enter Email Address');
                $this->messageManager->addErrorMessage($message);
                $this->logger->debug($e->getMessage());
        }
    }

    public function getQuotes()
    {
        return $this->checkoutSession->getQuote();
    }
}
