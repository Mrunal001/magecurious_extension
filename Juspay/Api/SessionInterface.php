<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Api;

/**
 * @api
 */
interface SessionInterface
{
    /**
     * Retrieve information about a post.
     *
     * @param  string $orderId             The ID of the order.
     * @param  float  $amount              The amount of the order.
     * @param  string $customerId          The ID of the customer.
     * @param  string $customerEmail       The Email Address of the customer.
     * @param  string $customerPhone       The Phone Number of the customer.
     * @param  string $paymentPageClientId The Payment Page Client ID.
     * @param  string $action              The action of the customer.
     * @param  string $returnURL           The return url.
     * @param  string $description         The description.
     * @param  string $firstname           The first name of the customer.
     * @param  string $lastname            The last name of the customer.
     * @return string
     */
    public function getPost($orderId, $amount, $customerId, $customerEmail, $customerPhone, $paymentPageClientId, $action, $returnURL, $description, $firstname, $lastname): \GuzzleHttp\Psr7\Response;
}
