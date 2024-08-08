<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Plugin\Sales\Model;

use Magento\Sales\Model\Order;

/**
 * Consider Store Credit Amount For Refund
 */
class OrderPlugin
{
    const EPSILON = 0.00001;

    public function afterCanCreditmemo(Order $subject, $result)
    {
        if (!$result || $subject->getAmstorecreditRefundedBaseAmount() === null) {
            return $result;
        }

        $storecreditInvoiced = (float)$subject->getAmstorecreditInvoicedBaseAmount();
        $storecreditRefunded = (float)$subject->getAmstorecreditRefundedBaseAmount();

        if ($this->isGreater($storecreditInvoiced, $storecreditRefunded)) {
            return true;
        }

        return $result;
    }

    /**
     * Compares two float digits.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     * @since 101.0.6
     */
    private function isEqual(float $a, float $b): bool
    {
        return abs($a - $b) <= self::EPSILON;
    }

    /**
     * Compares if the first argument greater than the second argument.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     * @since 101.0.6
     */
    private function isGreater(float $a, float $b): bool
    {
        return ($a - $b) > self::EPSILON;
    }

    /**
     * Compares if the first argument greater or equal to the second.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     * @since 101.0.6
     */
    private function isGreaterThanOrEqual(float $a, float $b): bool
    {
        return $this->isEqual($a, $b) || $this->isGreater($a, $b);
    }
}
