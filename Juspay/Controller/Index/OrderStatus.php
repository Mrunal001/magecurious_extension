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
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magecurious\Juspay\Helper\Data;

class OrderStatus extends Action
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
        $client = $this->clientFactory->create(['base_uri' => self::API_REQUEST_URI, 'debug' => true]);

        try {
                $orderId = $this->getRequest()->getParam('order_id');

                $apikey = $this->data->apiKey();
                $merchantid = $this->data->merchantKey();

                $response = $client->request(
                    'GET',
                    'https://api.juspay.in/orders/' . $orderId,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($apikey),
                            'x-merchantid' => $merchantid,
                            'version' => '2023-12-28',
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                    ]
                );
                

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['status']) && $responseData['status'] === 'CHARGED') {
                $order = $this->orderRepository->get($orderId);
                if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->orderRepository->save($order);
                }

                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success', ['order_id' => $orderId]);
                return $resultRedirect;
            }

            if (isset($responseData['status']) && $responseData['status'] === 'PENDING_VBV') {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/failure', ['order_id' => $orderId]);
                return $resultRedirect;
            }

            if (isset($responseData['status']) && $responseData['status'] === 'AUTHENTICATION_FAILED') {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/failure', ['order_id' => $orderId]);
                return $resultRedirect;
            }

            if (isset($responseData['status']) && $responseData['status'] === 'AUTHORIZATION_FAILED') {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/failure', ['order_id' => $orderId]);
                return $resultRedirect;
            }
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
