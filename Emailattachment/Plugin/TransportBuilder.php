<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Plugin;

use Magecurious\Emailattachment\Helper\Data;

class TransportBuilder
{
    protected $_dataHelper;
    public $attachmentManager;
    private $scopeConfig;

    public function __construct(
        \Magecurious\Emailattachment\Model\AttachmentManager $attachmentManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $dataHelper
    ) {
        $this->attachmentManager = $attachmentManager;
        $this->scopeConfig = $scopeConfig;
        $this->_dataHelper = $dataHelper;
    }

    public function beforeSetTemplateIdentifier($subject, $templateId)
    {
        $this->attachmentManager->resetParts();
        $this->attachmentManager->setTemplateId($templateId);
        return [$templateId];
    }

    public function beforeSetTemplateVars($subject, $templateVars)
    {
        $this->attachmentManager->setTemplateVars($templateVars);
        return [$templateVars];
    }

    public function afterGetTransport($subject, $result)
    {
        $ccRecipient = $this->_dataHelper->emailCc();
        $bccRecipient = $this->_dataHelper->emailBcc();
        

        if ($ccRecipient) {
            $result->getMessage()->addCc($ccRecipient, '');
        }

        if ($bccRecipient) {
            $result->getMessage()->addBcc($bccRecipient, '');
        }
        
        return $result;
    }
}
