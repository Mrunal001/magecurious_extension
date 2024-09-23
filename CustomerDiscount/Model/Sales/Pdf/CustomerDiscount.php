<?php

/**
 * Magecurious_CustomerDiscount
 *
 * @package Magecurious\CustomerDiscount
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\CustomerDiscount\Model\Sales\Pdf;

use \Magecurious\CustomerDiscount\Helper\Data;

class CustomerDiscount extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    public function __construct(Data $helper)
    {
            $this->helper = $helper;
    }

    public function getTotalsForDisplay()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->log(100, print_r("hello", true));
        $dis = $this->helper->getCustomDiscountTitle();
        $storeManager->log(100, print_r($dis, true));

        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        // $title = __($this->getTitle());
        // if ($this->getTitleSourceField()) {
        //     $label = $title . " (" . $this->getTitleDescription() . "):";
        // } else {
        //     $label = $title . ":";
        // }
        $discount = $this->helper->getCustomDiscountTitle();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = [
            "amount" => $amount,
            "label" => $discount,
            "font_size" => $fontSize,
        ];
        return [$total];
    }
}
