<?php
/**
 * Copyright © CheckoutPlaceHolder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CheckoutPlaceHolder\Plugin\Frontend\Magento\Checkout\Block\Checkout;

class AttributeMerger
{

    public function afterMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
        $result
    ) {
        
        if (array_key_exists('firstname', $result)) {
            $result['firstname']['additionalClasses'] = 'checkout-firstname';
        }

        if (array_key_exists('lastname', $result)) {
            $result['lastname']['additionalClasses'] = 'checkout-lastname';
        }

        if (array_key_exists('country_id', $result)) {
            $result['country_id']['additionalClasses'] = 'checkout-country_id';
        }
        
        if (array_key_exists('city', $result)) {
            $result['city']['additionalClasses'] = 'checkout-city';
        }

        if (array_key_exists('custom_attributes.custom_field_1', $result)) {
            $result['custom_attributes.custom_field_1']['additionalClasses'] = 'checkout-custom_field_1';
        }

        if (array_key_exists('custom_attributes.custom_field_2', $result)) {
            $result['custom_attributes.custom_field_2']['additionalClasses'] = 'checkout-custom_field_2';
        }

        if (array_key_exists('kl_sms_consent', $result)) {
            $result['custom_attributes.kl_sms_consent']['additionalClasses'] = 'checkout-kl_sms_consent';
        }

        if (array_key_exists('street', $result)) {
            $result['street']['children'][0]['additionalClasses'] = 'checkout-street0';
        }

        if (array_key_exists('street', $result)) {
            $result['street']['children'][1]['additionalClasses'] = 'checkout-street1';
        }

        if (array_key_exists('street', $result)) {
            $result['street']['children'][2]['additionalClasses'] = 'checkout-street2';
        }

        if (array_key_exists('telephone', $result)) {
            $result['telephone']['additionalClasses'] = 'checkout-telephone';
        }

        if (array_key_exists('postcode', $result)) {
            $result['postcode']['additionalClasses'] = 'checkout-postcode';
        }

        if (array_key_exists('postcode', $result)) {
            $result['postcode']['additionalClasses'] = 'checkout-postcode';
        }
        
        $result['custom_attributes.custom_field_2']['validation'] = [
            'required-entry'  => true,
            'validate-number' => true
        ];

        $result['street']['children'][0]['validation'] = [
             "required-entry" => true, 
             "min_text_length" => 3
        ];

        $result['telephone']['placeholder'] = __('Write your the primary number');
        $result['custom_attributes.custom_field_2']['placeholder'] = __('Please write your second number');
        return $result;
    }
}

