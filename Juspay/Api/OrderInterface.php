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
interface OrderInterface
{
    /**
     * Retrieve information about a post.
     *
     * @param string $orderId The ID of the order.
     * @return string
     */
    public function getPost($orderId): \GuzzleHttp\Psr7\Response;
}
