<?php

/**
 * Magecurious_Juspay
 *
 * @package Magecurious\Juspay
 * @author  Magecurious <support@magecurious.com>
 */

namespace Magecurious\Juspay\Plugin\Magento\Framework\Data\Form\FormKey;

class ValidatorPlugin
{
    /**
     * @param  \Magento\Framework\Data\Form\FormKey\Validator $subject
     * @param  \Magento\Framework\App\RequestInterface        $request
     * @return array
     */
    public function afterValidate(
        \Magento\Framework\Data\Form\FormKey\Validator $subject,
        $result,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $formKey = $request->getParam('form_key', null);

        if ($request->getActionName() === 'orderstatus') {
            return true;
        }
        
        return [$request];
    }
}
