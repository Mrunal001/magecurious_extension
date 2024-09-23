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
interface RefundInterface
{
    /**
     * Retrieve information about a post.
     *
     * @param string $orderId The ID of the order.
     * @param float  $amount  The amount of the order.
     *
     * @return string
     */
    public function getPost($orderId, $amount): \GuzzleHttp\Psr7\Response;
}
