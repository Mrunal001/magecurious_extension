<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = 'emailattachment/general/enable';

    const EMAIL_CC = 'emailattachment/general/cc';

    const EMAIL_BCC = 'emailattachment/general/bcc';

    const ENABLE_PDF = 'emailattachment/general/enable_attach_pdf';

    const EMAIL_PDF_FOR = 'emailattachment/general/attach_pdf_for';

    const EMAIL_COPY = 'emailattachment/general/email_copy';

    const ENABLE_TERMS = 'emailattachment/general/enable_attach_tc';

    const ATTACH_TC_FOR = 'emailattachment/general/attach_tc_for';

    const FILE_UPLOAD = 'emailattachment/general/file_upload';
    
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
 
    /*
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::MODULE_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function emailCc()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function emailBcc()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_BCC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * @return bool
     */
    public function enableAttachPDF()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_PDF,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function attachPDF()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_PDF_FOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function emailCopy()
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_COPY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function enableAttachTC()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_TERMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function attachTC()
    {
        return $this->scopeConfig->getValue(
            self::ATTACH_TC_FOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function fileUpload()
    {
        return $this->scopeConfig->getValue(
            self::FILE_UPLOAD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
