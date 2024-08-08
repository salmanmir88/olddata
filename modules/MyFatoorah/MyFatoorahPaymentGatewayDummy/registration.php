<?php

if (!defined('MYFATOORAH_LOG_FILE')) {
    define('MYFATOORAH_LOG_FILE', BP . '/var/log/myfatoorah.log');
}

/**
 * This file is responsible for updating the MyfatoorahLibrary file everyday 
 *
 * MyFatoorah offers a seamless business experience by offering a technology put together by our tech team. This enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing. MyFatoorah invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low-cost.
 * Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.
 *
 * Created by MyFatoorah http://www.myfatoorah.com/
 * Developed By tech@myfatoorah.com
 * Date: 17/01/2022
 * Time: 12:00
 *
 * API Documentation on https://myfatoorah.readme.io/docs
 * Library Documentation and Download link on https://myfatoorah.readme.io/docs/php-library
 * 
 * @author MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license GNU General Public License v3.0
 */

$mfLibFolder = dirname(__DIR__) . '/Library';
if (class_exists('\MyFatoorah\Library\PaymentMyfatoorahApiV2D')) {
    $mfLibFolder = dirname(\MyFatoorah\Library\PaymentMyfatoorahApiV2D::$pmCachedFile);
}

if (!file_exists($mfLibFolder)) {
    mkdir($mfLibFolder);
}

$mfLibFile = $mfLibFolder . '/MyfatoorahApiV2D.php';

$checking = true;
if (!file_exists($mfLibFile) || (time() - filemtime($mfLibFile) > 86400)) {

    $mfCurl = curl_init('https://portal.myfatoorah.com/Files/API/php/library/2.0.0/MyfatoorahLibrary.txt');
    curl_setopt_array($mfCurl, array(
        CURLOPT_RETURNTRANSFER => true,
    ));

    $mfResponse = curl_exec($mfCurl);
    $mfHttpCode = curl_getinfo($mfCurl, CURLINFO_HTTP_CODE);

    curl_close($mfCurl);
    if ($mfHttpCode == 200) {

        $mfNamespace = '<?php namespace MyFatoorah\Library; ';
        $mfUse1      = 'use MyFatoorah\Library\MyfatoorahApiV2D; ';
        $mfUse2      = 'use Exception; ';
        $mfClass     = 'class ';

        $mfSplitFile = explode('class', $mfResponse);
         
        $mfSplitFile1 = str_replace("MyfatoorahApiV2","MyfatoorahApiV2D",$mfSplitFile[1]);
        
        $mfSplitFile2 = str_replace("MyfatoorahApiV2","MyfatoorahApiV2D",$mfSplitFile[2]);
        $mfSplitFile2 = str_replace("mf-methods.json","mf-methods2.json",$mfSplitFile2);
        

        file_put_contents($mfLibFolder . '/MyfatoorahApiV2D.php', $mfNamespace . $mfUse2 . $mfClass . $mfSplitFile1);
        file_put_contents($mfLibFolder . '/PaymentMyfatoorahApiV2D.php', $mfNamespace . $mfUse1 . $mfUse2 . $mfClass . $mfSplitFile2);
        file_put_contents($mfLibFolder . '/ShippingMyfatoorahApiV2.php', $mfNamespace . $mfUse1 . $mfClass . $mfSplitFile[3]);
    } else if ($mfHttpCode == 403) {
        touch($mfLibFile);
    }
}

\Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'MyFatoorah_MyFatoorahPaymentGatewayDummy',
        __DIR__
);
