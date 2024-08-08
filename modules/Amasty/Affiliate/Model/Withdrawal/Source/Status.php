<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Withdrawal\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Withdrawal
     */
    private $banner;

    /**
     * @var \Amasty\Affiliate\Model\Withdrawal
     */
    private $withdrawal;

    /**
     * Status constructor.
     * @param \Amasty\Affiliate\Model\Withdrawal $withdrawal
     */
    public function __construct(\Amasty\Affiliate\Model\Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $availableOptions = $this->withdrawal->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
