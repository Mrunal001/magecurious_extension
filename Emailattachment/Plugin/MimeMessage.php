<?php

/**
 * Magecurious_Emailattachment
 *
 * @package Magecurious\Emailattachment
 * @author  magecurious<support@magecurious.com>
 */

namespace Magecurious\Emailattachment\Plugin;

class MimeMessage
{
    public $attachmentManager;

    public function __construct(
        \Magecurious\Emailattachment\Model\AttachmentManager $attachmentManager
    ) {
        $this->attachmentManager = $attachmentManager;
    }

    public function afterGetParts($subject, $parts)
    {
        if (!empty($parts) && $this->attachmentManager->getParts() === null) {
            $this->attachmentManager->collectParts();
            $additionalParts = $this->attachmentManager->getParts();
            if (!empty($additionalParts)) {
                foreach ($additionalParts as $aPart) {
                    $parts[] = $aPart;
                }
            }
        }

        return $parts;
    }
}
