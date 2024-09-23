<?php

/**
 * Magecurious_Limitproductquantity
 *
 * @package Magecurious\Limitproductquantity
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Limitproductquantity\Model\ProductLink\CollectionProvider;

class CustomLinkProducts
{
    public function getLinkedProducts($product)
    {
        return $product->getCustomLinkProducts();
    }
}
