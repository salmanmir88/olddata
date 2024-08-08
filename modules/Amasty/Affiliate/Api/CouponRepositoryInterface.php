<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Api;

interface CouponRepositoryInterface
{
    /**
     * @param \Amasty\Affiliate\Api\Data\CouponInterface $coupon
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\CouponInterface $coupon);

    /**
     * @param int $couponId
     * @return \Amasty\Affiliate\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($couponId);

    /**
     * @param \Amasty\Affiliate\Api\Data\CouponInterface $coupon
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\CouponInterface $coupon);
}
