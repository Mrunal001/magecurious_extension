<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\UrlInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magecurious\Juspay\Helper\Data;

class Session extends Action
{
    const API_REQUEST_URI = 'https://api.juspay.in/';

    private $responseFactory;
    private $clientFactory;
    private $json;
    private $orderRepository;
    private $checkoutSession;
    private $cartrepository;
    private $quoteRepository;
    protected $resultFactory;
    protected $_url;
    protected $data;

    public function __construct(
        Context $context,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Json $json,
        OrderRepository $orderRepository,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartrepository,
        QuoteRepository $quoteRepository,
        ResultFactory $resultFactory,
        UrlInterface $url,
        Data $data
    ) {
        parent::__construct($context);
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cartrepository = $cartrepository;
        $this->quoteRepository = $quoteRepository;
        $this->resultFactory = $resultFactory;
        $this->_url = $url;
        $this->data = $data;
    }

    public function execute()
    {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->debug("juspay");

        if ($this->data->isEnabled()) {
            $client = $this->clientFactory->create(['base_uri' => self::API_REQUEST_URI, 'debug' => true]);

            try {
                    $orderIdParam = $this->checkoutSession->getLastOrderId();
                    $order = $this->orderRepository->get($orderIdParam);
    
                    $orderId = $order->getEntityId();
                    $grandTotal = $order->getGrandTotal();
                    $customerId = $order->getCustomerId();
                    $customerEmail = $order->getCustomerEmail();
                    $customerPhone = $order->getBillingAddress()->getTelephone();
                    $firstName = $order->getCustomerFirstname();
                    $lastName = $order->getCustomerLastname();
                    $currencyCode = $order->getOrderCurrencyCode();
    
                    $apikey = $this->data->apiKey();
                    $merchantid = $this->data->merchantKey();
    
                    $response = $client->request(
                        'POST',
                        'https://api.juspay.in/session',
                        [
                            'json' => [
                                'order_id'               => $orderId,
                                'amount'                 => $grandTotal,
                                'customer_id'            => $customerId,
                                'customer_email'         => $customerEmail,
                                'customer_phone'         => $customerPhone,
                                'payment_page_client_id' => 'magecurious',
                                'action'                 => 'paymentPage',
                                'return_url'             => $this->_url->getUrl('juspay/index/orderstatus'),
                                'description'            => 'Payment for order #',
                                'first_name'             => $firstName,
                                'last_name'              => $lastName,
                                'currency'               => $currencyCode,
                            ],
                            'headers' => [
                                'Authorization'  => 'Basic ' . base64_encode($apikey),
                                'x-merchantid'    => $merchantid,
                                'Content-Type'    => 'application/json',
                            ],
                        ]
                    );
                    
    
                    $responseData = json_decode($response->getBody(), true);
    
                    $paymentLink = $responseData['payment_links']['web'];
    
                if (isset($responseData['status']) && $responseData['status'] === 'NEW') {
                    $order = $this->orderRepository->get($orderId);
                    if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                        $this->orderRepository->save($order);
                    }
                }
    
                    $redirectUrl = $this->_url->getUrl('juspay/index/orderstatus', ['order_id' => $orderId]);
    
                    return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                        [
                        'success' => true,
                        'payment_link' => $paymentLink,
                        ]
                    );
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($paymentLink);
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($redirectUrl);
            } catch (GuzzleException $exception) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                    [
                    'error' => true,
                    'message' => $exception->getMessage(),
                    ]
                );
    
                $response = $this->responseFactory->create(
                    [
                    'status' => $exception->getCode(),
                    'reason' => $exception->getMessage(),
                    ]
                );
    
                return $response;
            }
    
            return $response;
        }
    }
}
