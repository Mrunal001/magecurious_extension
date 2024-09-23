<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magecurious\Emailattachment\Helper\Data;

class AttachmentManager
{
    protected $_dataHelper;
    public $scopeConfig;
    public $invoicePdf;
    public $orderPdf;
    public $creditmemoPdf;
    public $shipmentPdf;
    public $mimePartInterfaceFactory;
    protected $filesystem;
    protected $directoryList;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Invoice $invoicePdf,
        Creditmemo $creditmemoPdf,
        Shipment $shipmentPdf,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        \Magecurious\Emailattachment\Model\OrderPdf $orderPdf,
        Data $dataHelper,
        Filesystem $filesystem,
        DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->invoicePdf = $invoicePdf;
        $this->orderPdf = $orderPdf;
        $this->creditmemoPdf = $creditmemoPdf;
        $this->shipmentPdf = $shipmentPdf;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->_dataHelper = $dataHelper;
    }


    private $templateId;

    private $templateVars = [];

    private $parts = null;

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    public function resetParts()
    {
        $this->parts = null;
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function addPart($part)
    {
        $this->parts[] = $part;
    }

    public function collectParts()
    {
        if ($this->parts !== null) {
            return;
        }
        
        $this->parts = [];
        $invoiceTemplateId = $this->getConfigValue(
            InvoiceIdentity::XML_PATH_EMAIL_TEMPLATE,
            $this->getStoreId()
        );

        $orderTemplateId = $this->getConfigValue(
            OrderIdentity::XML_PATH_EMAIL_TEMPLATE,
            $this->getStoreId()
        );

        $creditmemoTemplateId = $this->getConfigValue(
            CreditmemoIdentity::XML_PATH_EMAIL_TEMPLATE,
            $this->getStoreId()
        );

        $shipmentTemplateId = $this->getConfigValue(
            ShipmentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $this->getStoreId()
        );

        switch ($this->getTemplateId()) {
            case $invoiceTemplateId:
                $this->attachInvoicePDF();
                $attachtcinvoicearray = [];
                $attachtcpdf = $this->_dataHelper->attachTC();
                $checkininvoicetc = 'invoice';
                if (!empty($attachtcpdf)) {
                    $attachtcinvoicearray = explode(',', $attachtcpdf);
                }
                if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachTC() && in_array($checkininvoicetc, $attachtcinvoicearray)) {
                    $this->attachCustomPDF();
                }
                break;
            case $orderTemplateId:
                $order = $this->getTemplateVars()['order'];
                $this->attachOrderPDF($order);
                $attachtcorderarray = [];
                $attachtcpdforder = $this->_dataHelper->attachTC();
                $checkinordertc = 'order';
                if (!empty($attachtcpdforder)) {
                    $attachtcorderarray = explode(',', $attachtcpdforder);
                }
                if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachTC() && in_array($checkinordertc, $attachtcorderarray)) {
                    $this->attachCustomPDF();
                }
                break;
            case $creditmemoTemplateId:
                $this->attachCreditmemoPDF();
                $attachcreditmemoarray = [];
                $attachtcpdfcreditmemo = $this->_dataHelper->attachTC();
                $checkincreditmemotc = 'creditmemo';
                if (!empty($attachtcpdfcreditmemo)) {
                    $attachcreditmemoarray = explode(',', $attachtcpdfcreditmemo);
                }
                if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachTC() && in_array($checkincreditmemotc, $attachcreditmemoarray)) {
                    $this->attachCustomPDF();
                }
                break;
            case $shipmentTemplateId:
                $this->attachShipmentPDF();
                $attachshipmentarray = [];
                $attachtcpdfshipemt = $this->_dataHelper->attachTC();
                $checkinshipmenttc = 'shipment';
                if (!empty($attachtcpdfshipemt)) {
                    $attachshipmentarray = explode(',', $attachtcpdfshipemt);
                }
                if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachTC() && in_array($checkinshipmenttc, $attachshipmentarray)) {
                    $this->attachCustomPDF();
                }
                break;
        }
    }

    public function getStoreId()
    {
        $vars = $this->getTemplateVars();
        if (!isset($vars['store'])) {
            return null;
        }

        $store = $vars['store'];
        return $store->getId();
    }

    public function getConfigValue($path, $store = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
   
    public function attachInvoicePDF()
    {
        $attachinvoicepdfarray = [];
        $attachinvoicepdf = $this->_dataHelper->attachPDF();
        $checkininvoice = 'invoice';
        if (!empty($attachinvoicepdf)) {
            $attachinvoicepdfarray = explode(',', $attachinvoicepdf);
        }
        
        if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachPDF() && in_array($checkininvoice, $attachinvoicepdfarray)) {
            $vars = $this->getTemplateVars();
            $invoice = $vars['invoice'];
                
            $fileContent = $this->invoicePdf->getPdf([$invoice])->render();

            $invoiceId = $invoice->getIncrementId();
            $fileName = 'invoice_' . $invoiceId . '.pdf';
    
            $attachmentPart = $this->mimePartInterfaceFactory->create(
                [
                'content' => $fileContent,
                'type' => 'application/pdf',
                'fileName' => $fileName,
                'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
                ]
            );
    
                $this->addPart($attachmentPart);
        }
    }

    public function attachOrderPDF(Order $order)
    {
        $attachorderpdfarray = [];
        $attachorderpdf = $this->_dataHelper->attachPDF();
        $checkinorder = 'order';

        if (!empty($attachorderpdf)) {
            $attachorderpdfarray = explode(',', $attachorderpdf);
        }
       
        if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachPDF() && in_array($checkinorder, $attachorderpdfarray)) {
            $fileContent = $this->orderPdf->getPdf($order)->render();

            $orderId = $order->getIncrementId();
            $fileName = 'order_' . $orderId . '.pdf';

            $attachmentPart = $this->mimePartInterfaceFactory->create(
                [
                'content' => $fileContent,
                'type' => 'application/pdf',
                'fileName' => $fileName,
                'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
                ]
            );

            $this->addPart($attachmentPart);
        }
    }

    public function attachCreditmemoPDF()
    {
        $attachcreditmemopdfarray = [];
        $attachcreditmemopdf = $this->_dataHelper->attachPDF();
        $checkincreditmemo = 'creditmemo';
        if (!empty($attachcreditmemopdf)) {
            $attachcreditmemopdfarray = explode(',', $attachcreditmemopdf);
        }

        if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachPDF() && in_array($checkincreditmemo, $attachcreditmemopdfarray)) {
            $vars = $this->getTemplateVars();
            $creditmemo = $vars['creditmemo'];

            $pdf = $this->creditmemoPdf->getPdf([$creditmemo]);

            $fileContent = $pdf->render();

            $creditmemoId = $creditmemo->getIncrementId();
            $fileName = 'creditmemo_' . $creditmemoId . '.pdf';

            $attachmentPart = $this->mimePartInterfaceFactory->create(
                [
                    'content' => $fileContent,
                    'type' => 'application/pdf',
                    'fileName' => $fileName,
                    'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                    'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
                ]
            );

            $this->addPart($attachmentPart);
        }
    }

    public function attachShipmentPDF()
    {
        $attachshipmentdfarray = [];
        $attachshipmentpdf = $this->_dataHelper->attachPDF();
        $checkinshipment = 'shipment';
        if (!empty($attachshipmentpdf)) {
            $attachshipmentdfarray = explode(',', $attachshipmentpdf);
        }

        if ($this->_dataHelper->isEnabled() && $this->_dataHelper->enableAttachPDF() && in_array($checkinshipment, $attachshipmentdfarray)) {
            $vars = $this->getTemplateVars();
            $shipment = $vars['shipment'];

            $pdf = $this->shipmentPdf->getPdf([$shipment]);

            $fileContent = $pdf->render();

            $shipmentId = $shipment->getIncrementId();
            $fileName = 'shipment_' . $shipmentId . '.pdf';

            $attachmentPart = $this->mimePartInterfaceFactory->create(
                [
                    'content' => $fileContent,
                    'type' => 'application/pdf',
                    'fileName' => $fileName,
                    'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                    'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
                ]
            );

            $this->addPart($attachmentPart);
        }
    }

    public function attachCustomPDF()
    {
        $customPdfPath = $this->_dataHelper->fileUpload();
        if (!empty($customPdfPath)) {
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $baseMediaPath = $mediaDirectory->getAbsolutePath() . 'MyUploadDir/';
            $customPdfAbsolutePath = $baseMediaPath . basename($customPdfPath);
        
            if ($mediaDirectory->isFile($customPdfAbsolutePath)) {
                $fileContents = $mediaDirectory->readFile($customPdfAbsolutePath);
                $fileName = basename($customPdfPath);
            
                $attachmentPart = $this->mimePartInterfaceFactory->create(
                    [
                    'content' => $fileContents,
                    'type' => 'application/pdf',
                    'fileName' => $fileName,
                    'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                    'encoding' => \Zend\Mime\Mime::ENCODING_BASE64,
                    ]
                );

                $this->addPart($attachmentPart);
            }
        }
    }


    public function createAttachment($content, $type, $fileName)
    {
        return $this->mimePartInterfaceFactory->create(
            [
            'content' => $content,
            'type' => $type,
            'fileName' => $fileName,
            'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
            'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
            ]
        );
    }

    public function addAttachment($attachmentPart)
    {
        $this->addPart($attachmentPart);
    }
}
