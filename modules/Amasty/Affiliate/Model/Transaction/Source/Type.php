<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Transaction
     */
    private $transaction;

    /**
     * IsActive constructor.
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     */
    public function __construct(\Amasty\Affiliate\Model\Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $availableOptions = $this->transaction->getAvailableTypes();
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
