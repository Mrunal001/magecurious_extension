<?php

namespace Magecurious\Emailattachment\Model;

class RtlTextHandler
{
    /**
     * Reverse the RTL text.
     *
     * @param string $text
     * @return string
     */
    public function reverseRtlText(string $text): string
    {
        return strrev($text);
    }
}
