<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
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

class CreditMemoObserver implements ObserverInterface
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
    protected $messageManager;
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
        ManagerInterface $messageManager,
        Data $data
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cartrepository = $cartrepository;
        $this->quoteRepository = $quoteRepository;
        $this->resultFactory = $resultFactory;
        $this->_url = $url;
        $this->messageManager = $messageManager;
        $this->data = $data;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $client = $this->clientFactory->create(['base_uri' => self::API_REQUEST_URI, 'debug' => true]);

        try {
                $creditMemo = $observer->getEvent()->getCreditmemo();
                $orderId = $creditMemo->getOrderId();
                $grandTotal = $creditMemo->getBaseGrandTotal();

                $apikey = $this->data->apiKey();
                $merchantid = $this->data->merchantKey();

                $response = $client->request(
                    'POST',
                    'https://api.juspay.in/orders/' . $orderId . '/refunds',
                    [
                        'json' => [
                            'unique_request_id' => $orderId,
                            'amount' => $grandTotal,
                        ],
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($apikey),
                            'x-merchantid' => $merchantid,
                            'version' => '2024-01-02',
                            'Content-Type' => 'application/json',
                        ],
                    ]
                );

                $responseData = json_decode($response->getBody(), true);
            
            if (isset($responseData['refunds'][0]['status']) && $responseData['refunds'][0]['status'] == 'PENDING') {
                $message = __('Refund Initialize Successfully');
                $this->messageManager->addSuccess($message);
            } else {
                $message = __('Refund Initialize Not Successfully');
                $this->messageManager->addError($message);
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
