<?php
/**
 * Magecurious_AbandonedCarteMail
 *
 * @package Magecurious\AbandonedCarteMail
 * @author  Magecurious <support@magecurious.com>
 */
 
namespace Magecurious\AbandonedCarteMail\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
 
class Data extends AbstractHelper
{
    protected $encryptor;
    
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }
 
    /*
     * @return bool
     */
    public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        // Enable or Disable the Module
        return $this->scopeConfig->isSetFlag(
            'abandonedcartemail/general/enable',
            $scope
        );
    }

     /*
     * @return bool
     */
    public function isGuestEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        // Enable or Disable the Guest User Functionality
        return $this->scopeConfig->isSetFlag(
            'abandonedcartemail/general/guest_user',
            $scope
        );
    }
}
