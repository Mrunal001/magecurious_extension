<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */
 
namespace Magecurious\Emailattachment\Model\Config\Backend;

class CustomFileType extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    public function _getAllowedExtensions()
    {
        return ['pdf', 'txt', 'doc', 'jpeg', 'jpg', 'png', 'bmp', 'tiff'];
    }
}
