<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Plugin\Quote\Model;

use Amasty\Affiliate\Model\Validator\AffiliateCouponValidator;
use Magento\Framework\Exception\NoSuchEntityException;

class CouponManagementPlugin
{
    /**
     * @var AffiliateCouponValidator
     */
    private $affiliateCouponValidator;

    public function __construct(
        AffiliateCouponValidator $affiliateCouponValidator
    ) {
        $this->affiliateCouponValidator = $affiliateCouponValidator;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param int $cartId
     * @param string $couponCode
     * @throws NoSuchEntityException
     */
    public function beforeSet(\Magento\Quote\Model\CouponManagement $subject, $cartId, $couponCode)
    {
        if ($couponCode && !$this->affiliateCouponValidator->validate($couponCode)) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
    }
}
