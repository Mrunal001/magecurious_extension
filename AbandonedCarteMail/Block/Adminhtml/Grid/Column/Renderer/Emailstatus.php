<?php
/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */
 
namespace Magecurious\AbandonedCarteMail\Block\Adminhtml\Grid\Column\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
 
class Emailstatus extends AbstractRenderer
{
    // Set Email Status to Success or Pending in Abandoned Cart Report Page
    public function render(DataObject $row)
    {
        $status =  $row->getEmailStatus();

        if ($status !== "0") {
            $message_success = "Success";
            return $message_success;
        } else {
            $message_pending = "Pending";
            return $message_pending;
        }
    }
}
