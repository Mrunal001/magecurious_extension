<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Model;

use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\ScopeInterface;
use Magecurious\Emailattachment\Model\RtlTextHandler;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zend_Pdf;
use Zend_Pdf_Font;


class OrderPdf extends AbstractPdf
{
    protected $fileFactory;
    protected $filesystem;
    protected $checkoutSession;
    protected $quote;
    protected $dateTime;
    protected $appEmulation;
    protected $orderRepository;

    /**
     * Predefined constants
     */

    public const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';

    public const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';

    public const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

    /**
     * @var \Magento\Sales\Model\Order\Pdf\ItemsFactory
     */

    protected $_pdfItemsFactory;
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    protected $_pdfTotalFactory;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Config
     */
    protected $_pdfConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;
     /**
      * @var \Magento\Store\Model\StoreManagerInterface
      */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;
    /**
     * @var Database
     */
    private $fileStorageDatabase;
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;
    /**
     * @var RtlTextHandler
     */
    private $rtlTextHandler;
   
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;
    
    public function __construct(
        FileFactory $fileFactory,
        Filesystem $filesystem,
        Session $checkoutSession,
        DateTime $dateTime,
        AppEmulation $appEmulation,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $pdfConfig,
        Database $fileStorageDatabase = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        ?RtlTextHandler $rtlTextHandler = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->checkoutSession = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
        $this->dateTime = $dateTime;
        $this->appEmulation = $appEmulation;
        $this->inlineTranslation = $inlineTranslation;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_pdfConfig = $pdfConfig;
        $this->_storeManager = $storeManager;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDatabase = $fileStorageDatabase ?: ObjectManager::getInstance()->get(Database::class);
        $this->_scopeConfig = $scopeConfig;
        $this->addressRenderer = $addressRenderer;
        $this->_localeDate = $localeDate;
        $this->_paymentData = $paymentData;
        $this->string = $string;
        $this->_pdfItemsFactory = $pdfItemsFactory;
        $this->_pdfTotalFactory = $pdfTotalFactory;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->priceHelper = $priceHelper;
        $this->checkoutSession = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
        $this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
    }

     /**
      * Draw header for item table
      *
      * @param  \Zend_Pdf_Page $page
      * @return void
      */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => 360, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

     /**
      * Return PDF document
      *
      * @param  array|Collection $invoices
      * @return \Zend_Pdf
      */
    public function getPdf($invoices = [])
    {
        $x = 30;
        $this->y = $this->y - 245;

        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            $page = $this->newPage();
            $order = $this->orderFactory->create()->loadByIncrementId($invoices->getIncrementId());

            /* Add image */
            $this->insertinfoo($page);
            /* Add address */
            $this->insertAddress($page);
            /* Add head */
            $this->insertOrderCustom(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Order # ') . $order->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($order->getAllItems() as $item) {
                if ($item) {
                    $name = $item->getName();
                    $sku = $item->getSku();
                    $price = $item->getPrice();
                    $qty = (int) $item->getQtyOrdered();
                    $tax = $item->getTaxAmount();
                    $subtotal = $item->getRowTotal();
                }

                $page->drawText($name, $x + 5, $this->y, 'UTF-8');
                $page->drawText($sku, $x + 225, $this->y, 'UTF-8');
                $this->_setFontBold($page, 10);
                $page->drawText($this->priceHelper->currency($price, true, false), $x + 310, $this->y, 'UTF-8');
                $this->_setFontRegular($page, 10);
                $page->drawText($qty, $x + 390, $this->y, 'UTF-8');
                $this->_setFontBold($page, 10);
                $page->drawText($this->priceHelper->currency($tax, true, false), $x + 450, $this->y, 'UTF-8');
                $this->_setFontRegular($page, 10);
                $this->_setFontBold($page, 10);
                $page->drawText($this->priceHelper->currency($subtotal, true, false), $x + 505, $this->y, 'UTF-8');
                $this->_setFontRegular($page, 10);

                $this->y -= 20;

                /* Draw item */
                $page = end($pdf->pages);
            }
            /* Add totals */

            $subtotal = $order->getSubtotal();
            $discount = abs($order->getDiscountAmount());
            $shipping = $order->getShippingAmount();
            $grandTotal = $order->getGrandTotal();
            
            $subtotalText = 'Subtotal: ';
            $discountText = 'Discount (): ';
            $shippingText = 'Shipping & Handling: ';
            $grandTotalText = 'Total: ';

            $this->y -= 50;

            $lineBlock = ['lines' => [], 'height' => 15];

            $lineBlock['lines'][] = [
                [
                    'text' => $subtotalText,
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold',
                ],
                [
                    'text' => $this->priceHelper->currency($subtotal, true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
            
            $lineBlock['lines'][] = [
                [
                    'text' => $discountText,
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold',
                ],
                [
                    'text' => $this->priceHelper->currency(-$discount, true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
            
            $lineBlock['lines'][] = [
                [
                    'text' => $shippingText,
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold',
                ],
                [
                    'text' => $this->priceHelper->currency($shipping, true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
            
            $lineBlock['lines'][] = [
                [
                    'text' => $grandTotalText,
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold',
                ],
                [
                    'text' => $this->priceHelper->currency($grandTotal, true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 10,
                    'font' => 'bold'
                ],
            ];
            
            $page = $this->drawLineBlocks($page, [$lineBlock]);
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Returns the total width in points of the string using the specified font and size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param  string                  $string
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  float                   $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        // phpcs:ignore Generic.PHP.NoSilencedErrors
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv(
            'UTF-8',
            'UTF-16BE//IGNORE',
            $string
        // phpcs:ignore Generic.PHP.NoSilencedErrors
        ) : @iconv(
            'UTF-8',
            'UTF-16BE',
            $string
        );

        $characters = [];

        $drawingStringLength = strlen($drawingString);

        for ($i = 0; $i < $drawingStringLength; $i++) {
            $characters[] = ord($drawingString[$i++]) << 8 | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = array_sum($widths) / $font->getUnitsPerEm() * $fontSize;
        return $stringWidth;
    }

     /**
      * Calculate coordinates to draw something in a column aligned to the right
      *
      * @param  string                  $string
      * @param  int                     $x
      * @param  int                     $columnWidth
      * @param  \Zend_Pdf_Resource_Font $font
      * @param  int                     $fontSize
      * @param  int                     $padding
      * @return int
      */
    public function getAlignRight($string, $x, $columnWidth, \Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param  string                  $string
     * @param  int                     $x
     * @param  int                     $columnWidth
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  int                     $fontSize
     * @return int
     */
    public function getAlignCenter($string, $x, $columnWidth, \Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    /**
     * Insert infoo to pdf page
     *
     * @param                                        \Zend_Pdf_Page $page
     * @param                                        string|null    $store
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws                                       \Zend_Pdf_Exception
     */
    protected function insertinfoo(&$page, $store = null)
    {
        $this->y = $this->y ?: 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/infoo',
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/infoo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage()
                && !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 830;
                //top border of the page
                $widthLimit = 270;
                //half of the page width
                $heightLimit = 270;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     * Insert address to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  string|null    $store
     * @return void
     */
    protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ?: 815;
        $top = 815;
        $configAddress = $this->_scopeConfig->getValue(
            'sales/identity/address',
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $values = $configAddress ? explode("\n", $configAddress) : [];
        foreach ($values as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value ?: '')),
                        $this->getAlignRight($_value, 130, 440, $font, 10),
                        $top,
                        'UTF-8'
                    );
                    $top -= 10;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = [];
        $values = $address !== null ? explode('|', $address) : [];

        foreach ($values as $str) {
            foreach ($this->string->split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Calculate address height
     *
     * @param                                       array $address
     * @return                                      int Height
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _calcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }

    /**
     * Insert order to pdf page.
     *
     * @param                                         \Zend_Pdf_Page             $page
     * @param                                         \Magento\Sales\Model\Order $obj
     * @param                                         bool                       $putOrderId
     * @return                                        void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrderCustom(&$page, $order, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment;
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = $paymentInfo !== null ? htmlspecialchars_decode($paymentInfo, ENT_QUOTES) : '';
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if ($value && strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $this->prepareText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part ?: '')), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            $shippingAddress = $shippingAddress ?? [];
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->prepareText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part ?: '')), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if ($value && trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value ?: '')), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            if (isset($shippingMethod) && \is_string($shippingMethod)) {
                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value ?: '')), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];

            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $trackTitle = $track->getTitle() ?? '';
                    $endOfTitle = strlen($trackTitle) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($trackTitle, 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Insert title and number for concrete document type
     *
     * @param  \Zend_Pdf_Page $page
     * @param  string         $text
     * @return void
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Sort totals list
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    protected function _sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        return $a['sort_order'] <=> $b['sort_order'];
    }

    /**
     * Return total list
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[] Array of totals
     */
    protected function _getTotalsList()
    {
        $totals = $this->_pdfConfig->getTotals();
        usort($totals, [$this, '_sortTotalsList']);
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->_pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    /**
     * Parse item description
     *
     * @param  \Magento\Framework\DataObject $item
     * @return array
     */
    protected function _parseItemDescription($item)
    {
        $matches = [];
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return [$description];
    }

    /**
     * Before getPdf processing
     *
     * @return void
     */
    protected function _beforeGetPdf()
    {
        $this->inlineTranslation->suspend();
    }

    /**
     * After getPdf processing
     *
     * @return void
     */
    protected function _afterGetPdf()
    {
        $this->inlineTranslation->resume();
    }

    /**
     * Format option value process
     *
     * @param  array|string               $value
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function _formatOptionValue($value, $order)
    {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

     /**
      * Set font as regular
      *
      * @param  \Zend_Pdf_Page $object
      * @param  int            $size
      * @return \Zend_Pdf_Resource_Font
      */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerif.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int            $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerifBold.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int            $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerifItalic.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

     /**
      * Set PDF object
      *
      * @param  \Zend_Pdf $pdf
      * @return $this
      */
    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

     /**
      * Retrieve PDF object
      *
      * @throws \Magento\Framework\Exception\LocalizedException
      * @return \Zend_Pdf
      */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }

        return $this->_pdf;
    }

     /**
      * Create new page and assign to PDF object
      *
      * @param  array $settings
      * @return \Zend_Pdf_Page
      */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    /**
     * Draw lines
     *
     * Draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parameter), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param                                         \Zend_Pdf_Page $page
     * @param                                         array          $draw
     * @param                                         array          $pageSettings
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @return                                        \Zend_Pdf_Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        $this->pageSettings = $pageSettings;
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;
            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        //
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }
            $this->correctLines($lines, $page, $height);
        }

        return $page;
    }

    /**
     * Correct lines.
     *
     * @param                                        array          $lines
     * @param                                        \Zend_Pdf_Page $page
     * @param                                        int            $height
     * @throws                                       \Zend_Pdf_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function correctLines($lines, $page, $height) :void
    {
        foreach ($lines as $line) {
            $maxHeight = 0;
            foreach ($line as $column) {
                $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                if (!empty($column['font_file'])) {
                    $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                    $page->setFont($font, $fontSize);
                } else {
                    $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                    switch ($fontStyle) {
                        case 'bold':
                            $font = $this->_setFontBold($page, $fontSize);
                            break;
                        case 'italic':
                            $font = $this->_setFontItalic($page, $fontSize);
                            break;
                        default:
                            $font = $this->_setFontRegular($page, $fontSize);
                            break;
                    }
                }

                if (!is_array($column['text'])) {
                    $column['text'] = [$column['text']];
                }
                $top = $this->correctText($column, $height, $font, $page);

                $maxHeight = $top > $maxHeight ? $top : $maxHeight;
            }
            $this->y -= $maxHeight;
        }
    }

    /**
     * Correct text.
     *
     * @param                                        array                   $column
     * @param                                        int                     $height
     * @param                                        \Zend_Pdf_Resource_Font $font
     * @param                                        \Zend_Pdf_Page          $page
     * @throws                                       \Zend_Pdf_Exception
     * @return                                       int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function correctText($column, $height, $font, $page) :int
    {
        $top = 0;
        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
        $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
        foreach ($column['text'] as $part) {
            if ($this->y - $lineSpacing < 15) {
                $page = $this->newPage($this->pageSettings);
            }

            $feed = $column['feed'];
            $textAlign = empty($column['align']) ? 'left' : $column['align'];
            $width = empty($column['width']) ? 0 : $column['width'];
            switch ($textAlign) {
                case 'right':
                    if ($width) {
                        $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                    } else {
                        $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                    }
                    break;
                case 'center':
                    if ($width) {
                        $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                    }
                    break;
                default:
                    break;
            }
            $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
            $top += $lineSpacing;
        }
        return $top;
    }

    /**
     * Get title description from source
     *
     * @return mixed
     */
    public function getTitleDescription()
    {
        return $this->getSource()->getOrder()->getData($this->getTitleSourceField());
    }

    
    public function getTotalsForDisplayCustom()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }

     /**
      * Returns prepared for PDF text, reversed in case of RTL text
      *
      * @param  string $string
      * @return string
      */
    private function prepareText(string $string): string
    {
        return $this->rtlTextHandler->reverseRtlText(html_entity_decode($string));
    }
}
