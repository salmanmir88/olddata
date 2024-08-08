<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Plugin\CustomerData;

use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;
use MagePal\GoogleAnalytics4\CustomerData\JsDataLayer;

class JsDataLayerPlugin
{

    /**
     * @var EnhancedEcommerceSession
     */
    protected $enhancedEcommerceSession;

    /**
     * @param EnhancedEcommerceSession $enhancedEcommerceSession
     */
    public function __construct(
        EnhancedEcommerceSession $enhancedEcommerceSession
    ) {
        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
    }

    /**
     * @param JsDataLayer $subject
     * @param $result
     */
    public function afterGetSectionData(JsDataLayer $subject, $result)
    {
        $data = $this->enhancedEcommerceSession->getProductDataObjectArray();

        if (!empty($data)) {
            $result['cartItems'] = $data;
        }

        return $result;
    }
}
